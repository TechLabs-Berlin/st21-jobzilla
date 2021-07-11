<?php
/*
 * @author Mark Rahman
 */
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

class Dashboard extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        
    }

    public function index()
    {
        $this->load->view( "resume/upload-cv");
    }

    public function jobTitle()
    {
        $post = $this->input->post();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data['job_title'] = $post['job_title'];
            $data['description'] = $post['description'];

            if( isset($post['id']) && $post['id'] > 0){

                $this->db->where("id", $post['id']);
                $this->db->update("jobs", $data);
            }else{

                $this->db->insert("jobs", $data);
            }
            
            redirect("dashboard/jobList");
        }else{
            $this->load->view( "resume/job-title");
        }
        
        
    }

    public function uploadCv()
    {
        $post = $this->input->post();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $target = "tmp/";

            $target = $target.time().basename( $_FILES['uploaded_cv']['name']);
            if(move_uploaded_file($_FILES['uploaded_cv']['tmp_name'], $target)) 
            {

                $target  = realpath($target);

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://jzl-search-api-v7otpcjevq-lz.a.run.app/uploadfile/');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);


                $file = new CURLFile($target,'application/pdf',"MyFile");
                curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $file]);


                $headers = array();
                $headers[] = 'Accept: application/json'; 
                $headers[] = 'Content-Type: multipart/form-data';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                curl_close($ch);
                $decodeData = json_decode($result);
                $this->session->set_userdata("cv_skills", $decodeData);
                
                redirect("dashboard/jobSearch");
            }else{
                redirect("dashboard/uploadCv");
            }

        }else{
            $this->load->view( "resume/upload-cv");
        }

    }

    public function coverLetter()
    {
        $this->load->view( "resume/cover-letter");
    }

    public function jobList()
    {
        $data['jobList'] = $this->db->get_where("jobs")->result_array();

        $this->load->view( "resume/job-list", $data);
    }
    public function jobSearch()
    {
        $post = $this->input->post();

        $data['skills'] = $this->session->userdata('cv_skills');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data['title'] = $post['title'];
            $data['skill'] = $post['skill'];
            $data['city'] = $post['city'];

            // API URL
            $request = parse_url("https://jzl-search-api-v7otpcjevq-lz.a.run.app/jobsearch/?title=".$data['title']."&skills=".$data['skill']."&city=".$data['city']);


            // echo $request = "https://jzl-search-api-v7otpcjevq-lz.a.run.app/jobsearch/?title=data%20engineer&skills=sql%2C%20python&city=berlin";
            // echo $url = rawurlencode($url);
            // prd(rawurlencode($request["query"]));

            $query = "title=".$data['title']."&skills=".$data['skill']."&city=".$data['city'];
            $specChars = array(
                // '!' => '%21',    '"' => '%22',
                // '#' => '%23',    '$' => '%24',    '%' => '%25',
                // '&' => '%26',    '\'' => '%27',   '(' => '%28',
                // ')' => '%29',    '*' => '%2A',    '+' => '%2B',
                // ',' => '%2C',    '-' => '%2D',    '.' => '%2E',
                // '/' => '%2F',    ':' => '%3A',    ';' => '%3B',
                // '<' => '%3C',    '=' => '%3D',    '>' => '%3E',
                // '?' => '%3F',    '@' => '%40',    '[' => '%5B',
                // '\\' => '%5C',   ']' => '%5D',    '^' => '%5E',
                // '_' => '%5F',    '`' => '%60',    '{' => '%7B',
                // '|' => '%7C',    '}' => '%7D',    '~' => '%7E',
                ',' => '%2C',  ' ' => '%20'
            );

            foreach ($specChars as $k => $v) {
                $query = str_replace($k, $v, $query);
            }

            $url = "https://jzl-search-api-v7otpcjevq-lz.a.run.app/jobsearch/?".$query;

            
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

//for debug only!
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $resp = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($resp, true);

            $result['skills']= $data['skills'];
            $result['post']= $data;
            
            $this->load->view( "resume/job-search", $result);
            // redirect("dashboard/jobSearch");
        }else{
            $this->load->view( "resume/job-search", $data);
        }
    }

    public function jobEdit( $id )
    {

        $data['job'] = $this->db->get_where("jobs",[
            "id" => $id
        ])->row_array();

        $this->load->view( "resume/job-title", $data);
    }

    public function createCoverLetter()
    {

        $post = $this->input->post();

        //$data['html'] = $html;
        $data['html'] = $this->load->view( "resume/letter", $post);
        echo json_encode($data);
    }

    public function generateQuotationPdf ( $id, $isDownload, $onlineAcceptanceId = 0 ) {

        error_reporting(E_ERROR);
        $this->load->library('logistics/m_pdf');

        $pdf2 = $this->m_pdf->load_portait();
        //$pdf2->autoPageBreak = false;
        $pdf2->shrink_tables_to_fit = 0;

        $pdf2->shrink_tables_to_fit = 0;
        $pdf2->autoLangToFont = true;
        $pdf2->autoScriptToLang = true;
        $data = [];
        $file = [];

        $footerHtml = $this->load->view( loadSubView ("footer", $this->_className, "pdf"), $data, TRUE);;


        $mainHtml = $this->load->view( loadSubView ("main", $this->_className, "pdf"), $data, TRUE);
        // echo $mainHtml; die();
        $file_name = $data["details"]['number_with_prefix'].".pdf";

        $pdf2->setHTMLFooter($footerHtml);
            $pdf2->AddPage('P', '', '', '', '', 10, // left
            10, // right
            10, // top
            10, // bottom
            0,  // margin header
            5); // margin footer

            /* If Download PDF true/1 then pdf will be downloaded or will create a file in given directory */

            $pdf2->WriteHTML($mainHtml);

            $pdf2->Output('' . $file_name, 'D');

        }





    }
