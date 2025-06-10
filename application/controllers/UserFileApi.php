<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserFileApi extends CI_Controller {

    public $user;
    
    public function __construct(){
        parent::__construct();
        
        // 공통 설정 파일 로드
        $this->load->config('common');
        
        // 세션에서 사용자 정보 가져오기
        $this->user = $this->session->userdata('user');
    }
    
    public function index()
    {
            
    }
    
    // 파일 업로드 메서드
    public function fileUpload()
    {
        ini_set('memory_limit', '-1'); // 메모리 제한 해제
        $result = array('result' => false, 'msg' => "");                    

        // 이미지 리사이즈 라이브러리 로드
        $this->load->view('/libs/php-image-resize-master/ImageResize');
        
        // 업로드할 파일의 저장 경로
        $folderDir = "assets/upload/files";
        
        // 파일 업로드 처리
        $files = $this->realFileUpload($_FILES['files'], $folderDir, $_POST['pageCount']);
        
        // 파일 업로드 실패 시 메시지 반환
        if(!count($files)){
            $result['msg'] = '파일 업로드 도중 문제가 발생하였습니다.';
            die(json_encode($result));
        }
        
        // 업로드된 파일 정보 반환
        $result['files'] = $files;
        $result['result'] = true;
        
        die(json_encode($result)); // 결과를 JSON 형식으로 반환
    }
    
    // 실제 파일 업로드 처리 메서드
    private function realFileUpload($files, $folderDir, $pageCount){
        $fileArr = array();
        $filesLength = count($files['name']); // 업로드할 파일 수

        // 각 파일에 대해 반복하여 업로드 처리
        for($index=0; $index < $filesLength; $index++){
            $uploadsDir = FCPATH . $folderDir; // 업로드 경로
            $originalFileName = $files['name'][$index]; // 파일 원본 이름

            // 파일 확장자 추출
            $parts = explode('.', $originalFileName);
            $ext = strtolower(array_pop($parts));

            // 유니크한 파일 이름 생성 (현재 시간 + 랜덤 숫자)
            $num = sprintf("%06d", rand(0000, 9999));
            $fileName = strtotime("Now").'_'.$num.'_'.$index.'.'.$ext;
            $filePath = $uploadsDir.'/'.$fileName; // 최종 저장될 경로

            // 파일 이동
            if(move_uploaded_file($files['tmp_name'][$index], $filePath)){                                                

                // 이미지 파일일 경우 리사이즈
                $fileType = explode('/', $files['type'][$index])[0]; // 파일 타입 (image, video 등)

                if($fileType == 'image'){
                    $resizeImg = new \Gumlet\ImageResize($filePath);                    
                    $resizeImg->resizeToWidth(800); // 이미지 너비 800px로 리사이즈
                    $resizeImg->save($filePath); // 리사이즈된 이미지 저장
                }
                
                // 업로드된 파일 정보 저장
                $fileArr[] = array(
                    'ext' => $ext,                
                    'fileName' => $fileName,
                    'originalFileName' => $originalFileName,
                    'pageCount' => $pageCount[$index]
                );
            }
        }

        // 업로드된 파일 목록 반환
        return $fileArr;
    }
}
