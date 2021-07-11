<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Imap
{
	
	var $hostname	= "";
	var $username	= "";
	var $password	= "";
	protected $folder = 'INBOX';
	protected $config = [];

	//-------------------------------------------------------------------------------------
	public function get_emails($hostname,$username,$password)
	{
		$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: Not Connected to Enternet or '.imap_last_error());
		$emails = imap_search($inbox,'UNSEEN');
		$output = array();
		$ci= &get_instance();
		$id = '';
		if(is_array($emails)) 
		{
		    $i = 0;
		    $count = 1;
		    /* put the newest emails on top */
		    rsort($emails);
		    foreach($emails as $email_number) 
		    { 
		    	
		    	$overview = imap_fetch_overview($inbox,$email_number,0);
				$message = imap_fetchbody($inbox,$email_number,1.1);
				$header = imap_header($inbox, $email_number); // get first mails header

				/* output the email header information */
		
				$output[$i]['seen'] 	    = ($overview[0]->seen ? 'read' : 'unread');
				$output[$i]['from'] 	    = $overview[0]->from;
				$output[$i]['from_address'] = $header->from[0]->mailbox . "@" . $header->from[0]->host;
				$output[$i]['to'] 		    = $overview[0]->to;
				$output[$i]['date'] 	    = $overview[0]->date;
				$output[$i]['unix_date']    = $overview[0]->udate;
				$output[$i]['uid'] 		    = $overview[0]->uid;
				$output[$i]['body'] 	    =  $this->get_body($inbox,$overview[0]->uid);
				$msgid                      = imap_msgno($inbox, $overview[0]->uid);
				$files  = (array)$this->_get_attachments($overview[0]->uid, $inbox, $msgid , imap_fetchstructure($inbox, $msgid));	
				$ci->load->model('portal/FileUploaderModel');
		
			  	$pattern = '~[a-z]+://\S+~';

				if(preg_match_all($pattern, $output[$i]['body']['html'], $out)){

					$pos1 = strpos($output[$i]['body']['html'], 'id="AppleMailSignature"');
					$checkWordSection = strpos($output[$i]['body']['html'], 'WordSection1');
					$checkMorentzMail = strpos($output[$i]['body']['html'], '<blockquote type="cite"');

					if ($pos1 !== false) {
				     preg_match_all('/<body dir="auto">(.*?)<\/div>/s', $output[$i]['body']['html'], $replayMsg);	
                     $message_conversation_details = preg_replace('/<div dir="ltr">.*<\/div>/','', $replayMsg[0][0]);
 
					}else if ( $checkWordSection !== false ){
					    
					    $parseData = explode('<div style="border:none;border-top:solid #B5C4DF 1.0pt;padding:3.0pt 0cm 0cm 0cm">', $output[$i]['body']['html']);
					    $parseContent = explode('<div class="WordSection1">', $parseData[0]);
					    
					    $message_conversation_details = trim($parseContent[1]);
					    
					}else if ( $checkMorentzMail !== false ) {
					    
					    $parseData = explode('<blockquote type="cite"', $output[$i]['body']['html']);
					    $parseContent = explode("<table", $parseData[0]);

					    $message_conversation_details = strip_tags($parseContent[0], "<div><br>");

					} else{

						$msgData = explode('<div class="gmail_quote">', $output[$i]['body']['html']);
						$message_conversation_details = $msgData[0];
					}


					$pos = false;
					$availableCount = 0;
					
					for ( $linkCount = 0; $linkCount < sizeof($out[0]); $linkCount++ ) {
					    if ( false !== strpos($out[0][$linkCount], 'dbtables')) {
					        
					        $pos = strpos($out[0][$linkCount], 'dbtables');
					        $availableCount = $linkCount;
					    }
					    
					}

					
					$uri_segments = explode('/', $out[0][$availableCount]);

					if ($pos !== false) {
						 
							if($uri_segments[3] == 'dbtables'){
								$data['messages_id']                    = $uri_segments[7];
								$data['user_id']                        = $this->getUsedIdFromEmail($output[$i]['from_address']);
								$data['post_datetime']                  = time();
								$data['is_reply']                       = 1;
								$data['is_deleted']                     = 0;
								$data['account_id']                     = 1;
								$data['message_conversation_details']   = $message_conversation_details;

								$ci = & get_instance();
								$ci->db->insert('message_conversation_details', $data);
								$id= $ci->db->insert_id();

								foreach($files as $file){
									$fileName = preg_replace( '/[^A-Za-z0-9\-.]/', '_',$file['name']);
									$dir_to_save = FCPATH."tmp/file/".$fileName;
									file_put_contents($dir_to_save ,$file['content']);
									$response = $ci->FileUploaderModel->doUploadFile('tmp/file/'.$fileName, 'conversion_files', $uri_segments[6], $fileName );
			
									if($response ){
			
										$files_info['file_type'] = "";
										$files_info['file_location'] =  $response['filePath'];
										$files_info['msg_conversation_id'] = $id;
										$ci->db->insert('conversation_files', $files_info);
										
									}
			
									unlink($dir_to_save);
			
								}

								$this->addNotificationForReplayEmail($data['messages_id'],$data['user_id'], $id);
								
							}

					}

				}

				$this->mark_as_read($inbox,$overview[0]->uid);

				$i++;
				$count++;
			}
			return $id;
		 //   return $output;
		}
		else
		{
			return $output = "No Unread Email In Inbox.";
		}
		/* close the connection */
		imap_close($inbox);
	}
	
	public	function getUsedIdFromEmail($email)
	{
		$CI = get_instance();
		$CI->db->select('id');
		$CI->db->from('dashboard_login');
		$CI->db->where('email', $email);
		return $CI->db->get()->row()->id;
	
	}

	public function addNotificationForReplayEmail($message_id, $user_id, $message_conversation_id){

		$ci= &get_instance();
		$ci->db->select('*');
		$ci->db->from('message_thread_notification');
		$ci->db->where('messages_id', $message_id);
		$ci->db->where('is_deleted', 0);
		$result = $ci->db->get()->row_array();
		$sender_id = $this->get_userId($message_id);
		$string = str_replace($user_id, $sender_id, $result['user_group']);
		$ci->load->model('portal/Messages_model');
		
		

		$all_people_list = $ci->Messages_model->all_people_list("dashboard_login");
		$users = array();

		if ( $result['user_group'] == "*" ) {

			foreach ($all_people_list as $people) {
			
				$usersLists[] = $people['id'];
			}
		
		}else if ( $result['user_group'] == "0" )  {
			$usersLists = [];
		}else {
			$parsedData = explode(",", $string);
			foreach  ( $parsedData as $eachUser ) {

				$usersLists[] = str_replace ("#", "", $eachUser);
			}

		}
		
		$userNotificationId = 0;
		$usersLists = array_unique ( $usersLists );

		foreach ( $usersLists as $eachUser ) {

			if (  $user_id != $eachUser ) {

				$data['msg_conversation_id'] = $message_conversation_id;
				$data['is_notified'] = 0;
				$data['is_email_notified'] = 0;
				$data['post_datetime'] = date($ci->config->item('#EDIT_VIEW_DATE_TIME_FORMAT'));
				$data['user_id'] =  $eachUser;
				
				$ci->db->insert("user_notification", $data);
				
				if ( $userNotificationId == '0' ) {
	
					$userNotificationId = $ci->db->insert_id();
				}

			}

		}

		$url = base_url()."cron/send_email_notification/".$userNotificationId;

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$json_response = curl_exec($curl); 
	}
	
	public function get_userId($message_id)
	{
		$ci= &get_instance();
		$ci->db->select('*');
		$ci->db->from('message');
		$ci->db->where('id', $message_id);
		$ci->db->where('is_deleted', 0);
		$result = $ci->db->get()->row_array();
		return  $result['user_id'];
	
	}


	protected function get_body($inbox,int $uid)
	{
		return [
			'html'  => $this->get_part($inbox,$uid, 'TEXT/HTML'),
			'plain' => $this->get_part($inbox,$uid, 'TEXT/PLAIN'),
		];
	}

	public function mark_as_read($inbox,$uids)
	{
		return $this->message_setflag($inbox, $uids, 'Seen');
	}

	protected function message_setflag($inbox, $uids, string $flag)
	{
		if (is_array($uids))
		{
			$uids = implode(',', $uids);
		}

		return imap_setflag_full($inbox, str_replace(' ', '', $uids), '\\' . ucfirst($flag), ST_UID);
	}

	protected function get_part($inbox,int $uid, $mimetype = '', $structure = false, $part_number = '')
	{
		if (! $structure)
		{
			$structure = imap_fetchstructure($inbox, $uid, FT_UID);
		}

		if ($structure)
		{
			if ($mimetype === $this->get_mime_type($structure))
			{
				if (! $part_number)
				{
					$part_number = '1';
				}

				$text = imap_fetchbody($inbox, $uid, $part_number, FT_UID | FT_PEEK);

				return $this->struc_decoding($text, $structure->encoding);
			}

			if ($structure->type === TYPEMULTIPART) // 1 multipart
			{
				foreach ($structure->parts as $index => $subStruct)
				{
					$prefix = '';

					if ($part_number)
					{
						$prefix = $part_number . '.';
					}

					$data = $this->get_part($inbox,$uid, $mimetype, $subStruct, $prefix . ($index + 1));

					if ($data)
					{
						return $data;
					}
				}
			}
		}

		return false;
	}

	protected function struc_decoding(string $text, int $encoding = 5)
	{
		switch ($encoding)
		{
			case ENC7BIT: // 0 7bit
				return $text;
			case ENC8BIT: // 1 8bit
				return imap_8bit($text);
			case ENCBINARY: // 2 Binary
				return imap_binary($text);
			case ENCBASE64: // 3 Base64
				return imap_base64($text);
			case ENCQUOTEDPRINTABLE: // 4 Quoted-Printable
				return quoted_printable_decode($text);
			case ENCOTHER: // 5 other
				return $text;
			default:
				return $text;
		}
	}



	public function get_message($inbox, int $uid)
	{
		$cache_id = $this->folder . ':message_' . $uid;

		if (($cache = $this->get_cache($cache_id)) !== false)
		{
			return $cache;
		}

		// TODO: Maybe put this check before try get from cache
		// then we will know if the msg already exists
		$id = imap_msgno($inbox, $uid);

		// If id is zero the message do not exists
		if ($id === 0)
		{
			return false;
		}

		$header = imap_headerinfo($inbox, $id);

		// Check Priority
		preg_match('/X-Priority: ([\d])/mi', imap_fetchheader($inbox, $id), $matches);
		$priority = isset($matches[1]) ? $matches[1] : 3;

		$subject = '';

		if (isset($header->subject) && strlen($header->subject) > 0)
		{
			foreach (imap_mime_header_decode($header->subject) as $decoded_header)
			{
				$subject .= $decoded_header->text;
			}
		}

		$email = [
			'id'          => (int)$id,
			'uid'         => (int)$uid,
			'from'        => isset($header->from[0]) ? (array)$this->to_address($header->from[0]) : [],
			'to'          => isset($header->to) ? (array)$this->array_to_address($header->to) : [],
			'cc'          => isset($header->cc) ? (array)$this->array_to_address($header->cc) : [],
			'bcc'         => isset($header->bcc) ? (array)$this->array_to_address($header->bcc) : [],
			'reply_to'    => isset($header->reply_to) ? (array)$this->array_to_address($header->reply_to) : [],
			//'return_path' => isset($header->return_path) ? (array)$this->array_to_address($header->return_path) : [],
			'message_id'  => $header->message_id,
			'in_reply_to' => isset($header->in_reply_to) ? (string)$header->in_reply_to : '',
			'references'  => isset($header->references) ? explode(' ', $header->references) : [],
			'date'        => $header->date,//date('c', strtotime(substr($header->date, 0, 30))),
			'udate'       => (int)$header->udate,
			'subject'     => $this->convert_to_utf8($subject),
			'priority'    => (int)$priority,
			'recent'      => strlen(trim($header->Recent)) > 0,
			'read'        => strlen(trim($header->Unseen)) < 1,
			'answered'    => strlen(trim($header->Answered)) > 0,
			'flagged'     => strlen(trim($header->Flagged)) > 0,
			'deleted'     => strlen(trim($header->Deleted)) > 0,
			'draft'       => strlen(trim($header->Draft)) > 0,
			'size'        => (int)$header->Size,
			'attachments' => (array)$this->_get_attachments($uid, imap_fetchstructure($inbox, $id)),
			'body'        => $this->get_body($uid),
		];

		$email = $this->embed_images($email);

		for ($i = 0; $i < count($email['attachments']); $i++)
		{
			if ($email['attachments'][$i]['disposition'] !== 'attachment')
			{
				unset($email['attachments'][$i]);
			}
		}

		$this->set_cache($cache_id, $email);

		return $email;
	}


	protected function get_cache($cache_id)
	{
		if ($this->config['cache']['active'] === true)
		{
			return $this->CI->cache->get($cache_id);
		}

		return false;
	}

	protected function embed_images(array $email)
	{
		foreach ($email['attachments'] as $key => $attachment)
		{
			if ($attachment['disposition'] === 'inline' && ! empty($attachment['reference']))
			{
				$reference = str_replace(['<', '>'], '', $attachment['reference']);
				$img_embed = 'data:image/' . $attachment['type'] . ';base64,' . base64_encode($attachment['content']);

				$email['body']['html'] = str_replace('cid:' . $reference, $img_embed, $email['body']['html']);
			}
		}

		return $email;
	}

	protected function get_mime_type($structure)
	{
		$primary_body_types = [
			TYPETEXT        => 'TEXT',
			TYPEMULTIPART   => 'MULTIPART',
			TYPEMESSAGE     => 'MESSAGE',
			TYPEAPPLICATION => 'APPLICATION',
			TYPEAUDIO       => 'AUDIO',
			TYPEIMAGE       => 'IMAGE',
			TYPEVIDEO       => 'VIDEO',
			TYPEMODEL       => 'MODEL',
			TYPEOTHER       => 'OTHER',
		];

		if ($structure->ifsubtype)
		{
			return strtoupper($primary_body_types[(int)$structure->type] . '/' . $structure->subtype);
		}

		return 'TEXT/PLAIN';
	}
	// --------------------------------------------------------------------
	public function _check_attached_file($inbox, $email_number)
	{
		$structure = imap_fetchstructure($inbox, $email_number);
        $attachments = array();
        /* if any attachments found... */
		        if(isset($structure->parts) && count($structure->parts)) 
		        {
		            for($i = 0; $i < count($structure->parts); $i++) 
		            {
		                $attachments[$i] = array(
		                    'is_attachment' => false,
		                    'filename' => '',
		                    'name' => '',
		                    'attachment' => ''
		                );
		            
		                if($structure->parts[$i]->ifdparameters) 
		                {
		                    foreach($structure->parts[$i]->dparameters as $object) 
		                    {
		                        if(strtolower($object->attribute) == 'filename') 
		                        {
		                            $attachments[$i]['is_attachment'] = true;
		                            $attachments[$i]['filename'] = $object->value;
		                        }
		                    }
		                }
		            
		                if($structure->parts[$i]->ifparameters) 
		                {
		                    foreach($structure->parts[$i]->parameters as $object) 
		                    {
		                        if(strtolower($object->attribute) == 'name') 
		                        {
		                            $attachments[$i]['is_attachment'] = true;
		                            $attachments[$i]['name'] = $object->value;
		                        }
		                    }
		                }
		            
		                if($attachments[$i]['is_attachment']) 
		                {
		                    $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);
		                    
		                    /* 4 = QUOTED-PRINTABLE encoding */
		                    if($structure->parts[$i]->encoding == 3) 
		                    { 
		                        $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
		                    }
		                    /* 3 = BASE64 encoding */
		                    elseif($structure->parts[$i]->encoding == 4) 
		                    { 
		                        $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
		                    }
		                }
		            }
		        }
        
		        /* iterate through each attachment and save it */
		       // $file_name =  array();
		       $file_name ='';
		        foreach($attachments as $attachment)
		        {
		            if($attachment['is_attachment'] == 1)
		            {
		                $filename = $attachment['name'];
		                if(empty($filename)) $filename = $attachment['filename'];
		                
		                if(empty($filename)) $filename = time() . ".dat";
		                
		                /* prefix the email number to the filename in case two emails
		                 * have the attachment with the same file name.
		                 */
		                $file_name = $email_number . "-" . $filename;
		                $fp = fopen('media/'.$email_number . "-" . $filename, "w+");
		                fwrite($fp, $attachment['attachment']);
		                chmod('media/'.$email_number . "-" . $filename,0777);
		                fclose($fp);
		            }
		        }
		        return $file_name;
	}


	public function get_attachment(int $uid, int $index = 0)
	{
		$cache_id = $this->folder . ':message_' . $uid . ':attachment_' . $index;

		if (($cache = $this->get_cache($cache_id)) !== false)
		{
			return $cache;
		}

		$id         = imap_msgno($inbox, $uid);
		$structure  = imap_fetchstructure($inbox, $id);
		$attachment = $this->_get_attachments($uid, $structure, '', $index);

		$this->set_cache($cache_id, $attachment);

		if (empty($attachment))
		{
			return false;
		}

		return $attachment;
	}

	/**
	 * [get_attachments description]
	 *
	 * @param integer $uid
	 * @param array   $indexes
	 *
	 * @return array
	 */
	public function get_attachments(int $uid, array $indexes = [])
	{
		$attachments = [];

		foreach ($indexes as $index)
		{
			$attachments[] = $this->get_attachment($uid, (int)$index);
		}

		return $attachments;
	}

	/**
	 * [_get_attachments description]
	 *
	 * @param integer      $uid
	 * @param object       $structure
	 * @param string       $part_number
	 * @param integer|null $index
	 * @param boolean      $with_content
	 *
	 * @return array
	 */
	protected function _get_attachments(int $uid, $inbox, $id, $structure, string $part_number = '',	int $index = null)
	{
		
	//	$id          = imap_msgno($inbox, $uid);
		$attachments = [];

		if (isset($structure->parts))
		{
			foreach ($structure->parts as $key => $sub_structure)
			{
				$new_part_number = empty($part_number) ? $key + 1 : $part_number . '.' . ($key + 1);

				$results = $this->_get_attachments($uid, $inbox, $id, $sub_structure, $new_part_number);

				if (count($results))
				{
					if (isset($results[0]['name']))
					{
						foreach ($results as $result)
						{
							array_push($attachments, $result);
						}
					}
					else
					{
						array_push($attachments, $results);
					}
				}

				// If we already have the given indexes return here
				if (! is_null($index) && isset($attachments[$index]))
				{
					return $attachments[$index];
				}
			}
		}
		else
		{
			$attachment = [];

			if (isset($structure->dparameters[0]))
			{
				$bodystruct   = imap_bodystruct($inbox, $id, $part_number);
				$decoded_name = imap_mime_header_decode($bodystruct->dparameters[0]->value);
				$filename     = $this->convert_to_utf8($decoded_name[0]->text);
				$content      = imap_fetchbody($inbox, $id, $part_number);
				$content      = (string)$this->struc_decoding($content, $bodystruct->encoding);

				$attachment = [
					'name'         => (string)$filename,
					'part_number'  => (string)$part_number,
					'encoding'     => (int)$bodystruct->encoding,
					'size'         => (int)$structure->bytes,
					'reference'    => isset($bodystruct->id) ? (string)$bodystruct->id : '',
					'disposition'  => (string)strtolower($structure->disposition),
					'type'         => (string)strtolower($structure->subtype),
					'content'      => $content,
					'content_size' => strlen($content),
				];
			}

			return $attachment;
		}
	
		return $attachments;
	}

	protected function convert_to_utf8(string $str)
	{
		if (mb_detect_encoding($str, 'UTF-8, ISO-8859-1, GBK') !== 'UTF-8')
		{
			$str = utf8_encode($str);
		}

		$str = iconv('UTF-8', 'UTF-8//IGNORE', $str);

		return $str;
	}

}
// END CI_Imap class
/* End of file Imap.php */