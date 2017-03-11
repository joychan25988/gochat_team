<?php
error_reporting(E_ALL^E_NOTICE^E_WARNING);
class Admin extends CI_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->load->helper(array('form', 'url'));
        $this->load->model('admin_model');
    }

    public function upload()
    {
        $this->load->view('upload_form');
    }

    /*页面 主页*/
    public function index()
    {
        //CHECK SESSION
        $this->login_check();

        $UserId=$_SESSION['jm_admin_id'];

        //*************USERINFO******************
        //get USERINFO by userId for profile and username
        $data['userInfo'] = $this->admin_model->getUserInfo($UserId);
        $data['UserName'] = $data['userInfo']['UserName'];

        //*************FIRENDLIST******************
        //get friendlist
        $data['friend'] = $this->admin_model->getFriend($UserId);
        $str = $data['friend']['FriendId'];
            //split friendlist and get friendinfo
        $str1 = explode(",",$str);
        $i=0;
        foreach ($str1 as $friendList)
        {
            $data['friendInfo'][$i] = $this->admin_model->getUserInfo($friendList);
            $i++;
        }

        //*************NONE-GROUPCHAT******************
        //GET CHATID NONE-GROUP
        $data['chat_nogroup'] = $this->admin_model->getChatId_nogroup();
        //select userId by chatId from message table
        $i=0;
        foreach ($data['chat_nogroup'] as $nogroup){
            $data['RUList'][$i]= $this->admin_model->getUList($nogroup['ChatId'],$UserId);
            $i++;

        }
        //SELECT USERINFO
        $j=0;
        foreach ($data['RUList'] as $ru){
            $data['recent'][$j]= $this->admin_model->getUserInfo($ru['from']);
            $j++;
        }
//        var_dump($data['recent']);die;

        //*************GROUPCHAT******************
        //GET CHATID GROUP
        $data['chat_group'] = $this->admin_model->getChatId_group();

        //SELECT USERID BY CHATID FROM MESSAGE TABLE
        $i=0;
        foreach ($data['chat_group'] as $group){
            $data['groupList'][$i]= $this->admin_model->getGroupId($group['ChatId'],$UserId);
            $i++;
        }
        //SELECT USERINFO
        $j=0;

        foreach ($data['groupList'] as $gr){
            $m=0;
            foreach($gr as $g)
            {
//                $g[$m];
                $data['groupUser'][$j][$m]=$this->admin_model->getGroupUser($g['from']);
                $m++;
            }
            $j++;
        }
//        var_dump(sizeof($data['groupUser']));die;

        $this->load->view('index',$data);
    }


    /*页面 登录*/
    public function login()
    {
        $this->load->view('login');
    }

    public function login_check()
    {
        $session_admin = $this->session->userdata('jm_admin');
        if(!$session_admin){
            redirect('/admin/logintest');
        }
    }

    /*功能 登录*/
    public function login_do()
    {
        $username=  $this->input->post('username');
        $password = $this->input->post('password');

//        $username="admin";
//        $password ="admin";

        $loginInfo = array(

            'PIN'=> $password,
            'UserName'=>$username
        );
        $user = $this->admin_model->login_do($loginInfo);
        if(!count($user)){
            //false
            $data['status']=false;
//            redirect('admin/login');
        }
        else{
            //success
            $data['status']=true;
            //var_dump($user['nickName']);die;
            $this->session->set_userdata(array('jm_admin'=>$user['UserName'],'jm_admin_id'=>$user['UserId']));
//            redirect('admin/index');
        }
        $json = json_encode($data);
        print_r($json);
    }

    public function logout()
    {
        $this->session->sess_destroy();



            if(!empty($_COOKIE['jm_admin_id']) || !empty($_COOKIE['jm_admin'])){
                setcookie("jm_admin_id", null, time()-3600*24*365);
                setcookie("jm_admin", null, time()-3600*24*365);
            }

        redirect('login');
        return;
    }

    //getMessage
    public function getMessage()
    {
        $userId=  $this->input->post('userId');
        $friendId = $this->input->post('friendId');
//        $userId=1;
//        $friendId=3;

        $arr1 = array($userId,$friendId);
        $arr2 = array($friendId,$userId);
        $info1= implode(",",$arr1);
        $info2= implode(",",$arr2);
//        $member1="$info1";
//        $member2="$info2";
//        var_dump($member1);die;
        $data['userId'] = $userId;
        $data['ChatId'] = $this->admin_model->getChatId($info1,$info2);
        if($data['ChatId']!="")
        {
            $data['message'] = $this->admin_model->getMessage($data['ChatId']['ChatId']);
        }
        $i=0;
        foreach($data['message'] as $message)
        {
            $data['message'][$i]['profile']=$this->admin_model->getProfile($message['from']);
//            var_dump($message['profile']);
            $i++;
        }


//        var_dump($data['message']);die;


        $json = json_encode($data);
        print_r($json);
    }

    public function test()
    {
        echo "<h2>TCP/IP Connection</h2>\n";

        /* Get the port for the WWW service. */
//        $service_port = getservbyname('www', 'tcp');
        $service_port=9001;

        /* Get the IP address for the target host. */
        $address = gethostbyname('127.0.0.1');

        /* Create a TCP/IP socket. */
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
        } else {
            echo "OK.\n";
        }

        echo "Attempting to connect to '$address' on port '$service_port'...";
        $result = socket_connect($socket, $address, $service_port);
        if ($result === false) {
            echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
        } else {
            echo "OK.\n";
        }

        $in = "peggy";
        $in .= "$";
//        $in .= "11111";
        $out ="";

        echo "Sending HTTP HEAD request...";
        socket_write($socket, $in, strlen($in));
//        socket_write($socket, $in);
        echo "OK.\n";

//        echo "Reading response:\n\n";
//        while ($out = socket_read($socket, 2048)) {
//            echo $out;
//        }
//        $input = "peggy";
//        $input .= "$";
//        $input .= "111";
//        $output ='';
//        socket_write($socket, $input, strlen($in));

        echo "Reading response:\n\n";
        while ($out = socket_read($socket, 2048)) {
            echo $out;
        }
        echo "Closing socket...";
        socket_close($socket);
        echo "OK.\n\n";
}



    //upload file
    public function do_upload()
    {
        $config['upload_path']      = './uploads/';
        $config['allowed_types']    = 'gif|jpg|png|docx';
        $config['max_size']     = 1000;
        $config['max_width']        = 1024;
        $config['max_height']       = 768;

        $this->load->library('upload', $config);

        if ( ! $this->upload->do_upload('userfile'))
        {
            $error = array('error' => $this->upload->display_errors());

            $this->load->view('upload_form', $error);
        }
        else
        {
//            $data = array('upload_data' => $this->upload->data());

//            $this->load->view('upload_success', $data);
        }
    }

    //logintest
    public function logintest()
    {
        $this->load->view('logintest');
    }






}
?>
















