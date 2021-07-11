<?php
$this->load->helper('portal/user');
$user_helper = BUserHelper::get_instance(); ?>

<table cellspacing="0" cellpadding="0" class="m_1333569097778839761layout" style="background:#ffffff;margin:0;padding:0;border:0;text-align:left;border-collapse:collapse;border-spacing:0;width:100%">
  <tbody>
    <tr>
      <td style="background:#ffffff;text-align:left;vertical-align:top;font-size:15px;line-height:19px;font-family:&quot;Helvetica Neue&quot;,helvetica,arial,sans-serif;color:#000000;padding:0;border-spacing:0;border-collapse:collapse"><table cellspacing="0" cellpadding="0" class="m_1333569097778839761layout" style="background:#ffffff;margin:0;padding:0;border:0;text-align:left;border-collapse:collapse;border-spacing:0;width:100%">
          <tbody>
            <tr>
              <td><div>
                  <div> </div>
                  <div> </div>
                </div></td>
            </tr>
            <tr>
              <td style="background:#ffffff;text-align:left;vertical-align:top;font-size:15px;line-height:19px;font-family:&quot;Helvetica Neue&quot;,helvetica,arial,sans-serif;color:#000000;padding:0;border-spacing:0;border-collapse:collapse"><p style="font-family:&quot;Helvetica Neue&quot;,helvetica,arial,sans-serif;color:grey;font-size:12px;line-height:15px;margin:0 0 15px">
                <?php echo dashboard_lang("_WRITE_ABOVE_THIS_LINE_TO_POST_A_REPLY_OR");?> <a href="<?php echo $url; ?>" target="_blank"><?php echo dashboard_lang("_VIEW_THIS_ON");?> <?php echo $site_name; ?> </a> </p></td>
            </tr>
            <tr>
              <td style="background:#ffffff;text-align:left;vertical-align:top;font-size:15px;line-height:19px;font-family:&quot;Helvetica Neue&quot;,helvetica,arial,sans-serif;color:#000000;border-spacing:0;border-collapse:collapse;padding:20px 0"><table cellspacing="0" cellpadding="0" width="100%" style="background:#ffffff;margin:0;padding:0;border:0;text-align:left;border-collapse:collapse;border-spacing:0">
                  <tbody>
                    <tr>
                      <td width="45" valign="middle" style="font-size:15px;line-height:19px;font-family:&quot;Helvetica Neue&quot;,helvetica,arial,sans-serif;color:#000000;padding:0;border-spacing:0;border-collapse:collapse;background:#ffffff;text-align:left;width:45px;vertical-align:middle">
                          
                          <img class='img-responsive img-circle pull-left'  style='width:60px; height:60px; border-radius:50%; margin-right: 10px;' src='<?php echo $user_helper->render_profile_picture( $posted_user_profile_image ); ?>' alt="<?=@$posted_user_name?>" 
                              title="<?=@$posted_user_name?>" >
                          
                    </td>
                      <td valign="middle" width="100%" style="background:#ffffff;text-align:left;font-size:15px;line-height:19px;font-family:&quot;Helvetica Neue&quot;,helvetica,arial,sans-serif;padding:0;border-spacing:0;border-collapse:collapse;color:brown;vertical-align:middle;padding-left:10px;width:auto!important">
                         <?php echo $posted_details; ?>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="2" style="background:#ffffff;text-align:left;vertical-align:top;font-size:15px;line-height:19px;font-family:&quot;Helvetica Neue&quot;,helvetica,arial,sans-serif;color:#000000;border-spacing:0;border-collapse:collapse;padding:0 0 0 55px"><span>
                        <h3 style="font-weight:normal;font-family:&quot;Helvetica Neue&quot;,helvetica,arial,sans-serif;font-size:16px;line-height:20px;margin:0 0 5px;color:black"> 
                            <b> 
                              <?php echo $is_reply; ?><?php echo $msg_title;?> (<?php echo $user_name;?> ·<?php echo $posted_datetime ?>) 
                            </b> 
                        </h3>
                        <p style="font-family:&quot;Helvetica Neue&quot;,helvetica,arial,sans-serif;color:grey;font-size:12px;line-height:15px;margin:0 0 15px">
                          <?php echo dashboard_lang("_FROM");?>: <?php echo $entity;?>
                        </p>
                        </span>
                        <div style="padding-bottom:19px;border:none!important;padding:0!important;margin:0 0 19px!important;max-width:none!important">                           
                          <?php echo html_entity_decode($message_conversation_details);?>
                        </div>
                        <p style="font-size:15px;line-height:19px;font-family:&quot;Helvetica Neue&quot;,helvetica,arial,sans-serif;color:#000000;margin:0 0 19px"><a href="<?php echo $url;?>" target="_blank" ><?php echo dashboard_lang("_VIEW_THIS_ON");?> <?php echo $site_name; ?></a></p></td>
                    </tr>
                  </tbody>
                </table></td>
            </tr>
            <tr>
              <td style="background:#ffffff;text-align:left;vertical-align:top;font-size:15px;line-height:19px;font-family:&quot;Helvetica Neue&quot;,helvetica,arial,sans-serif;color:#000000;padding:0;border-spacing:0;border-collapse:collapse"><p style="font-family:&quot;Helvetica Neue&quot;,helvetica,arial,sans-serif;color:grey;font-size:12px;line-height:15px;margin:0 0 15px;margin-bottom:8px"> <?php echo dashboard_lang("_THIS_EMAIL_WAS_SENT_TO");?>: <?php echo  $user_list;?> </p></td>
            </tr>
          </tbody>
        </table></td>
    </tr>
  </tbody>
</table>

