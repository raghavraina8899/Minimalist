<?php

class Update extends Controller 
{
    public function index()
    {
        $data['page_title'] = "Update";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = $this->loadModel("user");
            $userModel->update($_POST['username'], $_POST['current_password'], $_POST['email'], $_POST['phone'], $_POST['new_password']);
        }
        
        $this->view("minima/update", $data);
    }
}
?>
