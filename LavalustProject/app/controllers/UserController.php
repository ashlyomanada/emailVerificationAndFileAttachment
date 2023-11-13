<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
use Dompdf\Dompdf;
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function

class UserController extends Controller {
   

    public function __construct() {
    parent::__construct();
        $this->call->model('User_model');
    }


    public function index(){
        return $this->call->view('useregister');

    }
    public function login(){
        return $this->call->view('userlogin');
    }

    public function home(){
        return $this->call->view('home');
    }

    public function createPdf(){
        $dompdf = new Dompdf();
        $dompdf->loadHtml('hello world');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream();
    }
    public function upload() {
        // Check if the file is selected
        if (isset($_FILES["userfile"])) {
            // Load the upload library
            $this->call->library('upload', $_FILES["userfile"]);
    
            // Configure upload settings
            $this->upload
                ->min_size(1)
                ->set_dir('public')
                ->allowed_extensions(array('jpg'))
                ->allowed_mimes(array('image/jpeg'))
                ->is_image()
                ->encrypt_name();
    
            // Attempt to upload the file
            if ($this->upload->do_upload()) {
                // File uploaded successfully
                $email = $this->io->post('email');
                $data['filename'] = $this->upload->get_filename();
    
                // Send email with the uploaded file link
                $this->call->library('email');
                $this->email->sender('ashlyomanada@gmail.com', 'Ashly Omanada');
                $this->email->recipient($email);
                $this->email->subject('Inbox');
                $this->email->email_content('<a href="' . site_url('public/' . $data['filename']) . '"><img src="' . site_url('public/' . $data['filename']) . '" alt="Image Description"></a>', 'html');
                $this->email->send();
    
                // Load the home view with data
                $this->call->view('welcome_page',$data);
            } else {
                // File upload failed, get errors and load the home view
                $data['errors'] = $this->upload->get_errors();
                $this->call->view('home', $data);
            }
        } else {
            // No file selected, handle accordingly
            $data['errors'][] = 'No file selected for upload.';
            $this->call->view('home', $data);
        }
    }
    
    public function signin(){
        $this->call->library('upload');
        $data['errors'] = $this->upload->get_errors();
                if($this->form_validation->submitted()) {
                    $this->form_validation->name('email')->required()
                                        ->name('password')->required();
                    if($this->form_validation->run()){
                        $email = $this->io->post('email');
                        $password = $this->io->post('password');
                        $result =  $this->User_model->checkuser($email, $password);
                        $result2 =  $this->User_model->checkVerify($email);
                    if($result && $result2){
                        return $this->call->view('home',$data);
                    }
                    else{
                        $this->call->view('userlogin');
                    }
                    }
                }
                else{
                    $this->call->view('userlogin');
                }
            }
       

    public function signup(){
        $username = $this->io->post('username');
        $email = $this->io->post('email');
        $verificationCode = substr(md5(rand()), 0, 8);
        if($this->form_validation->submitted()) {
            $this->form_validation->name('username')->required()
                                  ->name('email')->required()
                                  ->name('password')->required();
            if($this->form_validation->run()){
                $this->User_model->insert( $username, $email, $this->io->post('password'), $this->io->post('verified'),$verificationCode);
                
                $this->call->view('useregister');
                $this->call->library('email');
                $this->email->sender('ashlyomanada@gmail.com', 'Ashly Omanada');
                $this->email->recipient( $email);
                $this->email->subject('Account Verification');
                $this->email->email_content('<a href="http://localhost/LavalustProject/verify/' . $verificationCode . '" >verify</a>', 'html');
                $this->email->send();
                echo 'Email sent';
            }
        }
    }
           
    public function edit($verificationCode){
        $result = $this->User_model->isverified( $verificationCode);
        if($result){
            return $this->call->view('userlogin');
        }
        return $this->call->view('userlogin');
    }
    
    /*
     $email = $this->io->post('email');
                $this->User_model->isverified($email);
                $this->call->view('userlogin');
   
    public function insert(){
            if($this->form_validation->submitted()) {
                $this->form_validation->name('username')->required()->name('password')->required();
                if($this->form_validation->run()){
                    $this->User_model->insert($this->io->post('username'), $this->io->post('password'));
                    $this->call->view('userview');
                }
            }
        }
    

    public function show(){
        $data = $this->User_model->show();
        $this->call->view('usertable', $data);
    }
       
    public function delete($id) {
        if($this->User_model->delete($id))
       redirect('userstable');
        exit;
    }

    public function update_data(){
        $data = $this->User_model->show();
        if($this->form_validation->submitted()) {
            $this->form_validation->name('username')->required()->name('password')->required();
            if($this->form_validation->run()){
                $this->User_model->edit($this->io->post('userid'),$this->io->post('username'), $this->io->post('password'));
                $this->call->view('usertable', $data);
            }
            else{
                $this->call->view('hello');
            }
        }
        $this->call->view('userview');
    }
    */
}
?>