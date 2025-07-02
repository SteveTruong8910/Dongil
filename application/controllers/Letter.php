<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Letter extends CI_Controller {

    public $user;
    
	public function __construct(){
       	parent::__construct();
		
		$this->load->config('common');
        $this->user = getUserInfo($this->session->userdata('user'), $this->db);
    }
	
	public function index()
	{
        header('Content-Type: text/html; charset=UTF-8');
                
        $viewData = array('title' => '편지쓰기');
        
        $isLogin = empty($this->user)? 'N' : 'Y';
        $saveIdx = $_GET['idx']?? 0;
        $tmpSaveIdx = $_GET['tmpSaveIdx']?? 0;
        
        /* 관리자로 로그인 되어있는 경우에는 관리자페이지로 리디렉션 */ 
        if(!empty($this->user['id'])){
            header('Location: /admin');
            exit;
        }
                
        $info = array();
        
        /* 기본적으로 현금영수증 창 닫혀있게 처리 */
        $this->user['isCashReceipt'] = 'N';        
        
        /* 수정시(임시저장, 수정) */
        if(!empty($saveIdx) || !empty($tmpSaveIdx)) {
            $idx = empty($saveIdx)? $tmpSaveIdx : $saveIdx;
            
            $setSql = "";                                    
            
            $sql = "
                 SELECT
                    L.cateIdx,
                    W.*                    
                 FROM
                    write_list AS W LEFT JOIN
                    letter_list AS L ON L.idx = W.letterIdx
                 WHERE
                    W.idx = ? AND
                    W.state != 'F' AND
                    W.state != 'C'                    
            ";

            $query = $this->db->query($sql, array($idx));
            $info = $query->row_array();
            
            /* 
               1. 정보가 존재하지 않으면
               2. 임시저장 URL로 들어왔는데 임시저장 상태가 아닐 경우
               3. 수정 URL로 들어왔는데 임시저장 상태일 경우
               4. 저장 정보와 유저 정보가 일치하지 않으면
            */
            if( (empty($info)) ||
                (!empty($tmpSaveIdx) && $info['state'] != 'T') ||
                (!empty($saveIdx) && $info['state'] == 'T') ||
                ($info['memberIdx'] > 0 && (empty($this->user['idx']) || $info['memberIdx'] != $this->user['idx']))
            ) {
                header('Location: /');
                exit;
            }
            
            /* 편지 내용, 사진, PDF => JSON to array */
            $info['content'] = explode("$#", $info['content']);
            $info['photos'] = json_decode($info['photos'], true);
            if(empty($info['pdfFiles'])) {
                $info['pdfFiles'] = '[]';
            }
            $info['pdfFiles'] = json_decode($info['pdfFiles'], true);
            $info['libraryFiles'] = json_decode($info['libraryFiles'], true);

            /* 폰트정보 지정 */
            $this->user['fontKrName'] = $info['fontKrName'];
            $this->user['fontFamily'] = $info['fontFamily'];
            $this->user['fontName'] = $info['fontName'];
            
            /* 현금영수증 관련정보 지정 */
            $this->user['isCashReceipt'] = $info['isCashReceipt'];
            $this->user['cashReceiptType'] = $info['cashReceiptType'];
            $this->user['depositType'] = $info['depositType'];
            $this->user['cashReceiptNumber'] = $info['cashReceiptNumber'];
            $this->user['cashReceiptEmail'] = $info['cashReceiptEmail'];
        }
        
        /* 비회원 주문 강제 설정 (기본 - 프리텐다드) */
        if($isLogin == 'N'){
            $this->user['idx'] = 0;
            $this->user['senderAddr'] = '';
            $this->user['senderAddrDetail'] = '';
            $this->user['senderName'] = '';
            $this->user['senderTel'] = '';
            $this->user['receiverAddr'] = '';
            $this->user['receiverAddrDetail'] = '';
            $this->user['receiverName'] = '';
            $this->user['receiverTel'] = '';
            $this->user['point'] = 0;
            $this->user['addressIdx'] = 0;
            $this->user['isAllPoint'] = 'N';
            $this->user['fontKrName'] = '프리텐다드';
            $this->user['fontFamily'] = 'Pretendard-Regular';
            $this->user['fontName'] = 'Pretendard-Regular';            
            $this->user['cashReceiptType'] = '';
            $this->user['depositType'] = '';
            $this->user['cashReceiptNumber'] = '';
            $this->user['cashReceiptEmail'] = '';
        }else {
            if(empty($this->user['cashReceiptEmail'])) {
                $this->user['cashReceiptEmail'] = $this->user['mbEmail'];
            }
        }
        
        /* 주소록 */
        $postList = array();
        
        /* 카테고리 리스트 */
        $sql = "
             SELECT
                 *
             FROM
                 category_list
             WHERE
                 isUse = 'Y'
             ORDER BY
                sortOrder, idx ASC;
        ";
        
        $query = $this->db->query($sql);
        $cateList = $query->result_array();

        /* "전체" 탭을 추가 */
        if (!empty($cateList) && $cateList[0]['idx'] !== 0) {
            array_unshift($cateList, ['idx' => 0, 'cateName' => '전체']);
        }
        
        /* 처음 카테고리 강제지정(기본 편지지) */
        $firstCateIdx = $_GET['cateIdx'] ?? $cateList[1]['idx'];
        
        /* 편지지 리스트 (전체 선택 시 최신순으로 모든 편지지 불러오기) */
        if ($firstCateIdx == 0) {
            $sql = "
                SELECT
                    *
                FROM
                    letter_list
                WHERE
                    isUse = 'Y'
                ORDER BY
                    idx DESC;
            ";
            $query = $this->db->query($sql);
        } else {
            $orderBy = ($firstCateIdx == 1) ? "ASC" : "DESC";
            $sql = "
                SELECT
                    *
                FROM
                    letter_list
                WHERE
                    cateIdx = ? AND                
                    isUse = 'Y'
                ORDER BY
                    idx {$orderBy};
            ";
            $query = $this->db->query($sql, array($firstCateIdx));
        }

        $list = $query->result_array();
		
		for($i=0; $i<count($list); $i++){
			$row = $list[$i];
            
            /* 편지지 앞면, 뒷면 사진 정보 저장 */
            $row['thumbnail'] = json_decode($row['thumbnail'], true);
            $row['thumbnailBack'] = json_decode($row['thumbnailBack'], true);
                        
            if(!count($row['thumbnail'])){
                $row['imgPath'] = '/assets/image/no_image.jpg';
                $row['imgOnebonPath'] = '/assets/image/no_image.jpg';
            }else{
                $row['imgPath'] = '/assets/upload/'.$row['thumbnail'][0]['fileName'];
                $row['imgOnebonPath'] = '/assets/upload/'.$row['thumbnail'][0]['onebonFileName'];
            }
            
            if(!count($row['thumbnailBack'])){
                $row['imgPathBack'] = '';
                $row['imgOnebonPathBack'] = '';
            }else{
                $row['imgPathBack'] = '/assets/upload/'.$row['thumbnailBack'][0]['fileName'];    
                $row['imgOnebonPathBack'] = '/assets/upload/'.$row['thumbnailBack'][0]['onebonFileName'];
            }
            
            $row['regDate'] = substr($row['regDate'], 2, 14);
            
			$list[$i] = $row;
		}
        
        /* 폰트 즐겨찾기 리스트 */                        
        $sql = "
             SELECT
                *
             FROM
                favorite_font
             WHERE
                memberIdx = ? AND
                isUse = 'Y'
             ORDER BY
                idx DESC;
        ";
        
        $query = $this->db->query($sql, array($this->user['idx']));
        $favoriteFontList = $query->result_array();

        // 자료실 카테고리 불러오기
        $sql = "SELECT * FROM library_category WHERE isUse = 'Y' ORDER BY sortOrder ASC";
        $query = $this->db->query($sql);
        $libraryCateList = $query->result_array();

        // 전체 카테고리 강제 추가
        array_unshift($libraryCateList, ['idx' => 0, 'cateName' => '전체']);    

        // 첫 번째 카테고리 선택
        // $firstLibraryCateIdx = $_GET['libraryCateIdx'] ?? ($libraryCateList[0]['idx'] ?? 0);
        // 전체 카테고리의 idx는 0으로 간주
        $firstLibraryCateIdx = $_GET['libraryCateIdx'] ?? 0;


        // 자료 리스트 불러오기
        $sql = "
            SELECT * 
            FROM library_list
            WHERE isUse = 'Y' AND categoryIdx = ?
            ORDER BY sortOrder DESC, idx DESC
        ";
        $query = $this->db->query($sql, array($firstLibraryCateIdx));
        $libraryList = $query->result_array();

        // 썸네일 경로 구성
        foreach ($libraryList as &$item) {
            $item['thumbUrl'] = (strpos($item['thumbPath'], '/assets/') === 0)
                ? $item['thumbPath']
                : '/assets/upload/' . $item['thumbPath'];
        
            $item['fileUrl'] = (strpos($item['filePath'], '/assets/') === 0)
                ? $item['filePath']
                : '/assets/upload/' . $item['filePath'];

            $item['pageCount'] = isset($item['pageCount']) ? (int)$item['pageCount'] : 0;
        }
        

        // view에 전달
        $viewData['libraryCateList'] = $libraryCateList;
        $viewData['libraryList'] = $libraryList;
        $viewData['libraryCateIdx'] = $firstLibraryCateIdx;

                
        /* 편지 내용 viewData 지정 */
        $viewData['totalCnt'] = count($list);
        $viewData['cateList'] = $cateList;
        $viewData['list'] = $list;
        $viewData['isLogin'] = $isLogin;
        $viewData['postList'] = $postList;
        $viewData['favoriteFontList'] = $favoriteFontList;        
        
        /* 수정 및 임시저장 */
        $viewData['info'] = $info;
        $viewData['saveIdx'] = $saveIdx;
        $viewData['tmpSaveIdx'] = $tmpSaveIdx;
        
		$this->load->view('/common/header', $viewData);      
        if($this->config->item('isMe')) {
            $this->load->view('/letter/write', $viewData);
        }else {
            $this->load->view('/letter/write', $viewData);
        }
        
		$this->load->view('/common/footer');
	}
    
    /* react-native에서 결제 할 경우 페이지 */
    public function pay()        
    {
        // 뷰에 전달할 데이터 배열 초기화
        $viewData = array('title' => '');

        // GET 요청에서 writeIdx(글 인덱스) 가져오기
        $writeIdx = $_GET['writeIdx'];

        // 해당 글 정보 가져오기
        $sql = "SELECT * FROM write_list WHERE idx = ? ";        

        // SQL 실행 후 결과를 배열로 저장
        $query = $this->db->query($sql, array($writeIdx));
        $writeInfo = $query->row_array();

        // 최종 결제 금액 변수 초기화
        $realTotalPrice = 0;

        // 글이 수정 상태인지 확인
        if ($writeInfo['isSave'] == 'Y') {
            // 해당 글의 저장된 데이터 가져오기
            $sql = "SELECT * FROM write_tmp WHERE writeIdx = ? ORDER BY idx DESC; ";

            // SQL 실행 후 결과를 배열로 저장
            $query = $this->db->query($sql, array($writeIdx));
            $writeTmp = $query->row_array();

            // `queryString`에서 `realTotalPrice` 값 추출 (정규식 사용)
            $pattern = "/realTotalPrice = '(\d+)'/";
            if (preg_match($pattern, $writeTmp['queryString'], $matches)) {
                $realTotalPrice = (int)$matches[1];  // 문자열을 정수로 변환하여 저장
            }
        } else {
            // 수정 상태가 아닌 경우, 원래 글의 `realTotalPrice` 사용
            $realTotalPrice = $writeInfo['realTotalPrice'];
        }

        // 뷰에 전달할 데이터 설정
        $viewData['realTotalPrice'] = $realTotalPrice;
        $viewData['writeInfo'] = $writeInfo;

        // 뷰 로드 (헤더, 결제 페이지, 푸터)
        $this->load->view('/common/header', $viewData);
        $this->load->view('/letter/pay', $viewData);
        $this->load->view('/common/footer');
    }
}
