<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

    public $user;
    public $path;
    public $isPost = false;

    public function __construct()
    {
        // 부모 클래스의 생성자 호출
        parent::__construct();

        // 메모리 제한 해제 (무제한 메모리 사용)
        ini_set('memory_limit', '-1');

        // 공통 설정 파일 로드
        $this->load->config('common');

        // 세션에서 사용자 정보 가져오기
        $this->user = $this->session->userdata('user');

        // 현재 URL 경로를 가져오기
        $currentUrl = current_url();
        $this->path = parse_url($currentUrl, PHP_URL_PATH);

        /* 로그인 한 관리자만 접근 가능하도록 처리 */
        // 현재 경로가 /admin/login이 아니고, 로그인된 사용자가 admin이 아닌 경우 /admin/login으로 리디렉션
        if ($this->path != '/admin/login' && (empty($this->user['id']) || $this->user['id'] != 'admin')) {
            header('Location: /admin/login');
            exit;
        }
        // 현재 경로가 /admin/login이고 이미 admin으로 로그인된 경우, /admin으로 리디렉션
        else if ($this->path == '/admin/login' && isset($this->user['id']) && $this->user['id'] == 'admin') {
            header('Location: /admin');
            exit;
        }
    }

    public function index()
    {
        // 기본 페이지 로딩 시 /admin/post로 리디렉션
        header('Location: /admin/post');
        exit;
    }

    public function login()
    {
        // 뷰에 전달할 데이터 초기화
        $viewData = array();

        // 로그인 페이지 로드
        $this->load->view('/admin/header');
        $this->load->view('/admin/login', $viewData);
        $this->load->view('/admin/footer');
    }
    
    private function timeAgo($date) {
        // 입력된 날짜를 DateTime 객체로 변환
        $datetime = new DateTime($date);
        $now = new DateTime(); // 현재 시간
        $interval = $now->diff($datetime); // 시간 차이 계산

        // 시간 차이의 각 단위
        $seconds = $interval->s;
        $minutes = $interval->i;
        $hours = $interval->h;
        $days = $interval->d;
        $months = $interval->m;
        $years = $interval->y;

        if ($years > 0) {
            return $years . '년전';
        } elseif ($months > 0) {
            return $months . '달전';
        } elseif ($days > 0) {
            return $days . '일전';
        } elseif ($hours > 0) {
            return $hours . '시간전';
        } elseif ($minutes > 0) {
            return $minutes . '분전';
        } else {
            return $seconds . '초전';
        }
    }
    
    public function member()
    {
        // 뷰에 전달할 데이터를 담을 배열 초기화
        $viewData = array();

        // 페이지 번호, 검색 유형, 검색 텍스트, SNS, 회원 상태에 대한 파라미터 초기화
        $page = empty($_GET['page']) ? 1 : $_GET['page'];    
        $searchType = empty($_GET['searchType']) ? '' : $_GET['searchType'];
        $searchTxt = empty($_GET['searchTxt']) ? '' : $_GET['searchTxt'];
        $sns = empty($_GET['sns']) ? 'all' : $_GET['sns'];
        $mbState = empty($_GET['mbState']) ? 'all' : $_GET['mbState'];

        // 한 페이지에 표시할 항목 수와 페이지당 제한 설정
        $pagingCount = 50;
        $limit = getPageLimit($page, $pagingCount);

        // 검색 조건을 위한 SQL 부분 초기화
        $searchSql = "";        
        // 검색 유형과 검색 텍스트가 있을 경우 검색 조건에 추가
        if (!empty($searchType) && !empty($searchTxt)) {
            if ($searchType == 'idx') {
                $searchSql .= "{$searchType} = '{$searchTxt}' AND ";   
            } else {
                $searchSql .= "{$searchType} LIKE '%{$searchTxt}%' AND ";   
            }            
        }

        // SNS 필터링 조건 추가
        if ($sns != 'all') {
            $searchSql .= " sns = '{$sns}' AND ";
        }

        // 회원 상태에 따른 필터링 조건 추가 (사용 여부)
        if ($mbState == 'remove') {
            $searchSql .= " isUse = 'N' ";
        } else {
            $searchSql .= " isUse = 'Y' ";
        }

        // 회원 리스트 조회 SQL 쿼리
        $sql = "
            SELECT
                *
            FROM
                member_list
            WHERE
                $searchSql
            ORDER BY
                lastActivityDate DESC, idx DESC
            LIMIT
                $limit, $pagingCount;
        ";

        // 쿼리 실행하여 회원 목록을 가져옴
        $query = $this->db->query($sql);
        $list = $query->result_array();

        // 회원 목록을 순회하여 날짜 형식 등을 변경
        for ($i = 0; $i < count($list); $i++) {
            $row = $list[$i];

            // 활동 날짜를 '시간 전' 형식으로 변환
            $row['lastActivityDate'] = $this->timeAgo($row['lastActivityDate']);

            // 등록일을 2자리 년도 + 시간 형식으로 변환
            $row['regDate'] = substr($row['regDate'], 2, 14);

            // 변환된 값을 다시 배열에 저장
            $list[$i] = $row;
        }

        // 검색 조건에 맞는 회원 수를 조회하는 SQL 쿼리
        $sql = "
            SELECT
                COUNT(*) AS cnt
            FROM
                member_list
            WHERE
                $searchSql
        ";

        // 쿼리 실행하여 전체 회원 수를 가져옴
        $query = $this->db->query($sql);
        $totalCnt = $query->row_array()['cnt'];

        // 페이지 데이터와 검색 조건을 뷰에 전달할 데이터로 준비
        $viewData['pageData'] = array(
            'totalCnt' => $totalCnt,
            'page' => $page,
            'pagingCount' => $pagingCount
        );

        // 검색 조건 데이터를 뷰에 전달할 데이터로 준비
        $viewData['searchData'] = array(
            'searchType' => $searchType,
            'searchTxt' => $searchTxt,
            'sns' => $sns,
            'mbState' => $mbState
        );

        // 회원 목록을 뷰에 전달할 데이터로 준비
        $viewData['list'] = $list;

        // 헤더, 본문, 푸터 뷰를 로드하여 화면에 출력
        $this->load->view('/admin/header');
        $this->load->view('/admin/member', $viewData);
        $this->load->view('/admin/footer');
    }

    
    public function point()
    {
        // 뷰에 전달할 데이터를 저장할 배열 초기화
        $viewData = array();

        // URL에서 페이지 번호를 가져오고, 기본값은 1
        $page = empty($_GET['page'])? 1 : $_GET['page'];    

        // 검색 타입, 검색 텍스트, 상태를 URL에서 가져오고, 기본값은 각각 빈 값과 'wait'
        $searchType = empty($_GET['searchType'])? '' : $_GET['searchType'];
        $searchTxt = empty($_GET['searchTxt'])? '' : $_GET['searchTxt'];
        $state = empty($_GET['state'])? 'wait' : $_GET['state'];

        // 페이지당 표시할 레코드 수 설정
        $pagingCount = 50;

        // 페이지네이션을 위한 limit 계산
        $limit = getPageLimit($page, $pagingCount);

        // 검색 조건 SQL 초기화
        $searchSql = "";        
        if(!empty($searchType) && !empty($searchTxt)){
            // 검색 타입이 'P.memberIdx'이면 정확한 일치를, 그렇지 않으면 LIKE 검색 수행
            if($searchType == 'P.memberIdx') {
                $searchSql .= "{$searchType} = '{$searchTxt}' AND ";   
            } else {
                $searchSql .= "{$searchType} LIKE '%{$searchTxt}%' AND ";   
            }                        
        }

        // 상태가 'wait'이면 'isWait'이 'Y'인 조건 추가
        if($state == 'wait'){
            $searchSql .= " isWait = 'Y' AND ";
        }

        // 포인트 로그와 회원 리스트를 JOIN하여 데이터를 가져오는 SQL 쿼리
        $sql = "
             SELECT
                 P.*,
                 M.senderName
             FROM
                 point_log AS P JOIN
                 member_list AS M ON M.idx = P.memberIdx
             WHERE
                 $searchSql
                 P.isUse = 'Y'
             ORDER BY
                 P.idx DESC
             LIMIT
                $limit, $pagingCount;
        ";

        // 쿼리 실행 후 결과를 배열로 저장
        $query = $this->db->query($sql);
        $list = $query->result_array();

        // 각 항목의 등록 날짜를 포맷 변경
        for($i=0; $i<count($list); $i++){
            $row = $list[$i];                        
            $row['regDate'] = substr($row['regDate'], 2, 14);

            $list[$i] = $row;
        }

        // 총 레코드 수를 구하는 SQL 쿼리
        $sql = "
             SELECT
                 COUNT(*) AS cnt    
             FROM
                 point_log AS P JOIN
                 member_list AS M ON M.idx = P.memberIdx
            WHERE
                 $searchSql               
                 P.isUse = 'Y';
        ";

        // 쿼리 실행 후 결과에서 총 레코드 수를 가져옴
        $query = $this->db->query($sql);
        $totalCnt = $query->row_array()['cnt'];   

        // 페이지 데이터를 뷰에 전달할 배열로 설정
        $viewData['pageData'] = array(
            'totalCnt' => $totalCnt,
            'page' => $page,
            'pagingCount' => $pagingCount
        );

        // 검색 데이터 뷰에 전달할 배열로 설정
        $viewData['searchData'] = array(
            'searchType' => $searchType,
            'searchTxt' => $searchTxt,
            'state' => $state
        );

        // 포인트 내역 리스트 전달
        $viewData['list'] = $list;

        // 헤더, 본문, 푸터 뷰를 로드
        $this->load->view('/admin/header');
        $this->load->view('/admin/point', $viewData);
        $this->load->view('/admin/footer');
    }
    
    public function letter()
    {
        // 뷰 데이터를 담을 배열 초기화
        $viewData = array();

        // 카테고리 인덱스 및 테마 카테고리 인덱스 가져오기
        $viewData['cateIdx'] = $this->uri->segment(3, 0);
        $viewData['temaCateIdx'] = $this->uri->segment(4, 0);

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

        // 카테고리 리스트 쿼리 실행
        $query = $this->db->query($sql);
        $cateList = $query->result_array();

        // 카테고리가 없으면 첫 번째 카테고리로 설정
        if(empty($viewData['cateIdx'])){
            $viewData['cateIdx'] = $cateList[0]['idx'];
        }

        /* 테마 카테고리 */
        // 만약 카테고리가 3번이면 테마 카테고리 인덱스를 설정
        if($viewData['cateIdx'] == 3 && empty($viewData['temaCateIdx'])){
            $viewData['temaCateIdx'] = $cateList[3]['idx'];
        }

        /* 편지지 리스트 */

        $searchSql = "";

        // 테마 카테고리가 없으면 일반 카테고리로 검색
        if(empty($viewData['temaCateIdx'])){
            $searchSql = " cateIdx = '{$viewData['cateIdx']}' AND ";
        } else {
            // 테마 카테고리로 검색
            $searchSql = " cateIdx = '{$viewData['temaCateIdx']}' AND ";
        }

        // 편지지 리스트 쿼리 실행
        $sql = "
             SELECT
                 *
             FROM
                 letter_list
             WHERE
                 $searchSql
                 isUse = 'Y'
             ORDER BY
                sortOrder ASC, idx DESC;
        ";

        // 편지지 리스트 쿼리 실행
        $query = $this->db->query($sql);
        $list = $query->result_array();

        // 리스트에서 각 편지지의 이미지 경로 설정
        for($i = 0; $i < count($list); $i++){
            $row = $list[$i];

            // 썸네일 정보 처리 (배경, 원본 이미지 등)
            $row['thumbnail'] = json_decode($row['thumbnail'], true);
            $row['thumbnailBack'] = json_decode($row['thumbnailBack'], true);
            $row['thumbnailOriginal'] = json_decode($row['thumbnailOriginal'], true);

            // 썸네일 이미지가 없으면 기본 이미지 설정
            if(!count($row['thumbnail'])){
                $row['imgPath'] = '/assets/image/no_image.jpg';
            } else {
                $row['imgPath'] = '/assets/upload/'.$row['thumbnail'][0]['fileName'];
            }

            // 배경 이미지가 없으면 기본 이미지 설정
            if(!count($row['thumbnailBack'])){
                $row['imgBackPath'] = '/assets/image/no_image.jpg';
            } else {
                $row['imgBackPath'] = '/assets/upload/'.$row['thumbnailBack'][0]['fileName'];
            }

            // 원본 이미지가 없으면 기본 이미지 설정
            if(!count($row['thumbnailOriginal'])){
                $row['imgOnebonPath'] = '/assets/image/no_image.jpg';
            } else {
                $row['imgOnebonPath'] = '/assets/upload/'.$row['thumbnailOriginal'][0]['fileName'];
            }

            // 등록일을 부분적으로 처리하여 표시 (연도, 월, 일, 시간, 분까지)
            $row['regDate'] = substr($row['regDate'], 2, 14);

            // 수정된 row를 리스트에 반영
            $list[$i] = $row;
        }

        // 뷰 데이터에 카테고리, 편지지 리스트, 총 개수 설정
        $viewData['totalCnt'] = count($list);
        $viewData['list'] = $list;                
        $viewData['cateList'] = $cateList;

        // 헤더, 메인 내용, 푸터를 순서대로 로드
        $this->load->view('/admin/header');
        $this->load->view('/admin/letter', $viewData);
        $this->load->view('/admin/footer');
    }
    
    public function library()
    {
        // 뷰 데이터를 담을 배열 초기화
        $viewData = array();

        // 카테고리 인덱스 가져오기
        $viewData['categoryIdx'] = $this->uri->segment(3, 0);

        /* 카테고리 리스트 */
        $sql = "
            SELECT
                *
            FROM
                library_category
            WHERE                  
                isUse = 'Y'
            ORDER BY
                sortOrder, idx ASC;
        ";

        $query = $this->db->query($sql);
        $cateList = $query->result_array();

        if (empty($viewData['categoryIdx']) && count($cateList) > 0) {
            $viewData['categoryIdx'] = $cateList[0]['idx'];
        }

        /* 자료 리스트 */
        $sql = "
            SELECT
                *
            FROM
                library_list
            WHERE
                categoryIdx = '{$viewData['categoryIdx']}' AND
                isUse = 'Y'
            ORDER BY
                sortOrder ASC, idx DESC;
        ";

        $query = $this->db->query($sql);
        $list = $query->result_array();

        for ($i = 0; $i < count($list); $i++) {
            $row = $list[$i];

            // 썸네일 정보 처리 (PDF 첫 장 이미지)
            $row['thumbnail'] = json_decode($row['thumbnail'], true);
            if (!count($row['thumbnail'])) {
                $row['imgPath'] = '/assets/image/no_image.jpg';
            } else {
                $row['imgPath'] = '/assets/upload/' . $row['thumbnail'][0]['fileName'];
            }

            $row['regDate'] = substr($row['regDate'], 2, 14);
            $list[$i] = $row;
        }

        $viewData['totalCnt'] = count($list);
        $viewData['list'] = $list;
        $viewData['cateList'] = $cateList;

        // 뷰 로드
        $this->load->view('/admin/header');
        $this->load->view('/admin/library', $viewData);
        $this->load->view('/admin/footer');
    }

    
    public function post()
    {
        // 뷰에 전달할 데이터를 담을 배열 초기화
        $viewData = array();

        // 엑셀 다운로드 여부 확인 (파라미터 isExcel이 없으면 'N'으로 기본 설정)
        $isExcel = empty($_GET['isExcel']) ? 'N' : 'Y';

        // 현재 페이지 번호 (파라미터가 없으면 기본값 1)
        $page = empty($_GET['page']) ? 1 : $_GET['page'];    

        // 검색 유형 및 검색어 (기본값은 빈 문자열)
        $searchType = empty($_GET['searchType']) ? '' : $_GET['searchType'];
        $searchTxt = empty($_GET['searchTxt']) ? '' : $_GET['searchTxt'];

        // 상태 필터링 (기본값은 'W' - 대기 상태)
        $state = empty($_GET['state']) ? 'W' : $_GET['state'];

        // 색상 필터링 (기본값 'all' - 모든 색상)
        $color = empty($_GET['color']) ? 'all' : $_GET['color'];

        // 도장 여부 필터링 (기본값 99 - 모든 경우)
        $stamp = isset($_GET['stamp']) ? $_GET['stamp'] : 99;

        // 페이지당 표시할 데이터 개수 (기본값 100개)
        $pagingCnt = empty($_GET['pagingCnt']) ? 100 : $_GET['pagingCnt'];

        // 봉투 무게
        $envelopeWeightSort = empty($_GET['envelopeWeight']) ? 'all' : $_GET['envelopeWeight'];

        // 날짜 관련 변수 설정
        $today = date('Y-m-d'); // 오늘 날짜
        $day1ago = date('Y-m-d', strtotime('-1 day', strtotime($today))); // 하루 전 날짜
        $week1ago = date('Y-m-d', strtotime('-1 week', strtotime($today))); // 일주일 전 날짜

        // 배송일자 필터링 설정
        $dateState = empty($_GET['dateState']) ? 'ALL' : $_GET['dateState']; // 기본값: 전체('ALL')
        $startDate = empty($_GET['startDate']) ? $week1ago : $_GET['startDate']; // 기본값: 일주일 전 날짜
        $endDate = empty($_GET['endDate']) ? $today : $_GET['endDate']; // 기본값: 오늘 날짜

        // 완료일자 필터링 설정
        $finishDateState = empty($_GET['finishDateState']) ? 'ALL' : $_GET['finishDateState']; // 기본값: 전체('ALL')
        $finishStartDate = empty($_GET['finishStartDate']) ? $week1ago : $_GET['finishStartDate']; // 기본값: 일주일 전 날짜
        $finishEndDate = empty($_GET['finishEndDate']) ? $today : $_GET['finishEndDate']; // 기본값: 오늘 날짜

        // 봉투 종류 필터링 (기본값 'all' - 모든 봉투)
        $envelope = empty($_GET['envelope']) ? 'all' : $_GET['envelope'];

        // 페이지네이션을 위한 limit 설정 (현재 페이지 및 페이지당 개수 사용)
        $limit = getPageLimit($page, $pagingCnt);

        // 검색 및 limit 관련 SQL 조건문 초기화
        $searchSql = "";
        $limitSql = "";

        // 상태(state) 값에 따른 검색 조건 설정
        if ($state == 'W') { // 'W' = 대기 상태 + 결제 완료된 항목만 조회
            $searchSql = " (state = 'W' AND isPay = 'Y') AND ";
        } else if ($state == 'B') { // 'B' = 반려 상태
            $searchSql = " state = 'B' AND ";
        } else if ($state == 'A') { // 'A' = 진행 중이거나 완료된 상태 (단, 취소 상태 'T'는 제외)
            $searchSql = " ((state = 'W' AND isPay = 'Y') OR (state != 'W' AND state != 'T')) AND ";
        } else if ($state == 'C') { // 'C' = 취소 상태
            $searchSql = " state = 'C' AND ";
        } else { // 특정 상태를 지정한 경우 (기본적으로 결제 완료된 상태만 조회)
            $searchSql = " state = '{$state}' AND isPay = 'Y' AND ";               
        }

        // 배송일(dateState) 필터링 조건 추가
        if ($dateState == 'CHOICE') { // 사용자가 직접 선택한 기간 내 배송일 조회
            $searchSql .= "(W.deliveryDate >= '{$startDate}' AND W.deliveryDate <= '{$endDate}') AND ";   
        } else if ($dateState == 'TODAY') { // 오늘 배송 예정인 항목 조회
            $searchSql .= "W.deliveryDate = '{$today}' AND ";
        } else if ($dateState == 'YESTER') { // 어제 배송된 항목 조회
            $searchSql .= "W.deliveryDate = '{$day1ago}' AND ";
        }

        // 완료일(finishDateState) 필터링 조건 추가 (완료 상태 'F' 인 경우에만 적용)
        if ($state == 'F' && $finishDateState == 'CHOICE') {
            $searchSql .= "(DATE(W.finishDate) >= '{$finishStartDate}' AND DATE(W.finishDate) <= '{$finishEndDate}') AND ";   
        }

                         
        // 색상(color) 필터링 조건 추가
        switch($color) {
            case 'black': // 검정색
                $searchSql .= " L.cateIdx = 1 AND L.idx NOT IN (222) AND ";
                break;
            case 'kraft': // 크라프트
                $searchSql .= " L.idx = 222 AND ";
                break;
            case 'purple': // 보라색
                $searchSql .= " L.idx = 134 AND ";
                break;
            case 'grren': // (오타 가능성 있음) 초록색?
                $searchSql .= " L.idx = 133 AND ";
                break;
            case 'ivory': // 아이보리색
                $searchSql .= " L.idx = 132 AND ";
                break;
            case 'yellow': // 노란색
                $searchSql .= " L.idx = 35 AND ";
                break;
            case 'blue': // 파란색
                $searchSql .= " L.idx = 34 AND ";
                break;
            case 'pink': // 분홍색
                $searchSql .= " L.idx = 33 AND ";
                break;
            case 'tema': // 특정 카테고리(테마)
                $searchSql .= " L.cateIdx > 3 AND L.cateIdx != 38 AND ";
                break;
            case 'sdoku': // 스도쿠 관련 항목
                $searchSql .= " L.cateIdx = 38 AND L.name LIKE '%스도쿠%' AND ";
                break;
            case 'difference': // 숨은그림찾기 관련 항목
                $searchSql .= " L.cateIdx = 38 AND L.name LIKE '%숨은그림찾기%' AND ";
                break;
        }

        // 도장(stamp) 필터링 적용 (기본값 99는 모든 경우를 포함)
        if($stamp != 99) {
            $searchSql .= " W.stamp = '{$stamp}' AND ";
        }

        // 검색 조건 (searchType 및 searchTxt가 비어있지 않은 경우)
        if(!empty($searchType) && !empty($searchTxt)) {
            if($searchType == 'W.memberIdx') { // 회원 인덱스로 검색하는 경우 (정확한 매칭 필요)
                $searchSql .= "{$searchType} = '{$searchTxt}' AND ";
            } else { // 그 외 검색어가 포함된 경우 (LIKE 검색 적용)
                $searchSql .= "{$searchType} LIKE '%{$searchTxt}%' AND ";
            }
        }

        // 최대 편지 개수 설정값 가져오기
        $maxBigCnt = $this->config->item('maxBigCnt');

        // 봉투 크기(envelope) 필터링 적용
        switch($envelope) {
            case 'normal': // 일반 크기의 편지 (PDF 파일이 없거나 총 개수가 maxBigCnt 미만인 경우)
                $searchSql .= " W.totalPdfFileCnt = 0 AND W.totalLibraryFileCnt = 0 AND (W.totalLetterCnt + W.totalPhotoCnt) < {$maxBigCnt} AND ";
                break;
            case 'big': // 대형 봉투 (PDF 파일이 포함되거나, 편지+사진 개수가 maxBigCnt 이상인 경우)
                $searchSql .= " (W.totalPdfFileCnt > 0 OR W.totalLibraryFileCnt > 0 OR (W.totalLetterCnt + W.totalPhotoCnt) >= {$maxBigCnt}) AND ";
                break;
        }

        // 전체 보기(isAllView) 여부 설정
        // 엑셀 다운로드가 아닌 경우 && 특정 상태(W, I, P)가 아닌 경우 isAllView = 'N'
        $isAllView = ($isExcel == 'N' && $state != 'W' && $state != 'R' && $state != 'S' && $state != 'I' && $state != 'P') ? 'N' : 'Y';

        // 기본 정렬 순서
        $ORDER = 'ASC';

        // 전체 보기가 아닌 경우 (즉, 특정 필터링이 적용된 경우)
        if($isAllView == 'N') {
            $limitSql = " LIMIT $limit, $pagingCnt "; // 페이지네이션 적용
            $ORDER = 'DESC'; // 정렬을 내림차순으로 변경
        }

        // 엑셀 다운로드가 요청된 경우, 정렬 순서를 내림차순으로 변경
        if($isExcel == 'Y') {
            $ORDER = 'DESC';
        }
        
        // SQL 쿼리 작성: 글 목록 조회
        $sql = "
            SELECT
                W.*,
                L.cateIdx,
                L.name,                         
                CASE
                    WHEN W.totalPdfFileCnt > 0 OR W.totalLibraryFileCnt > 0 OR (W.totalLetterCnt + W.totalPhotoCnt) >= {$maxBigCnt} 
                      OR W.idx IN ('8464') -- 추후 삭제 필요
                    THEN 1 
                    ELSE 0 
                END AS isBig
            FROM
                write_list AS W
                LEFT JOIN letter_list AS L ON L.idx = W.letterIdx
            WHERE
                $searchSql
                W.isUse = 'Y'
            ORDER BY
                isBig ASC, 
                W.envelopeWeight ASC,
                CASE
                    WHEN W.stamp = 1 THEN 1
                    WHEN W.stamp = 2 THEN 2
                    WHEN W.stamp = 3 THEN 3
                    WHEN W.stamp = 0 THEN 4
                    ELSE 5
                END ASC,
                CASE
                    WHEN L.cateIdx = 1 AND L.idx != 222 THEN 1   -- 기본
                    WHEN L.idx = 222 THEN 2     -- 크라프트
                    WHEN L.cateIdx = 2 THEN 3   -- 심플
                    ELSE 4                      -- 나머지 테마
                END ASC,
                W.idx $ORDER
            $limitSql
        ";        

        // 쿼리 실행
        $query = $this->db->query($sql);
        $list = $query->result_array();

        // 데이터 후처리 (반복문을 통해 각 row에 추가적인 데이터를 설정)
        for($i = 0; $i < count($list); $i++) {
            $row = $list[$i];

            // writeId를 orderId 값으로 설정
            $row['writeId'] = $row['orderId'];

            // normalWriteId 생성 (등록 날짜 일부 + idx + 발신자명)
            $row['normalWriteId'] = unHyphen(substr($row['regDate'], 3, 7)) .
                                    $row['idx'] .
                                    $row['senderName'];

            // 날짜 형식 변경 (finishDate: YYMMDD 형식, regDate: YYMMDDHHMMSS 형식)
            $row['finishDate'] = substr($row['finishDate'], 2, 8);
            $row['regDate'] = substr($row['regDate'], 2, 14);

            // 사진 데이터 JSON 디코딩
            $row['photos'] = json_decode($row['photos'], true);
            $row['isGlossCnt'] = 0;
            $row['isNoneGlossCnt'] = 0;

            // 각 사진에 대해 광택 여부 카운트
            foreach ($row['photos'] as $key => $img) {
                if (empty($img['isGloss']) || $img['isGloss'] == 'Y') {
                    $row['isGlossCnt']++;
                } else {
                    $row['isNoneGlossCnt']++;
                }
            }

            $list[$i] = $row;
        }

        // 엑셀 다운로드가 아닌 경우 추가적인 데이터 조회 및 화면 출력
        if ($isExcel == 'N') {
            // 총 데이터 개수 조회 쿼리 실행
            $sql = "
                SELECT
                    COUNT(*) AS cnt    
                FROM
                    write_list AS W 
                    LEFT JOIN letter_list AS L ON L.idx = W.letterIdx
                WHERE
                    $searchSql
                    W.isUse = 'Y';
            ";

            $query = $this->db->query($sql);
            $totalCnt = $query->row_array()['cnt'];

            // 페이지네이션 데이터 설정
            $viewData['pageData'] = array(
                'totalCnt' => $totalCnt,
                'page' => $page,
                'pagingCount' => $pagingCnt
            );

            // 검색 필터 데이터 설정
            $viewData['searchData'] = array(
                'searchType' => $searchType,
                'searchTxt' => $searchTxt,
                'state' => $state,
                'dateState' => $dateState,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'finishDateState' => $finishDateState,
                'finishStartDate' => $finishStartDate,
                'finishEndDate' => $finishEndDate,
                'color' => $color,
                'stamp' => $stamp,
                'envelope' => $envelope,
                'pagingCnt' => $pagingCnt,
                'envelopeWeight' => $envelopeWeightSort,
            );

            // 리스트 데이터 설정
            $viewData['list'] = $list;                
            $viewData['isAllView'] = $isAllView;

            // 결제는 됐지만 대기 상태로 변경되지 않은 주문 확인
            checkPaymentOrder(0);

            // 관리자 페이지 뷰 로드
            $this->load->view('/admin/header');
            $this->load->view('/admin/post', $viewData);
            $this->load->view('/admin/footer');
        } else {            
            // 엑셀 다운로드 요청인 경우 JSON 데이터 반환
            $result['list'] = $list;
            echo json_encode($result);
        }
    }
    
    /********** 편지 출력 **********/
    private function getPostInfo($writeIdx) 
    {
        // 주어진 writeIdx에 해당하는 write_list 테이블에서 모든 정보를 조회
        $sql = "
             SELECT
                 *
             FROM
                 write_list
             WHERE
                 idx = '{$writeIdx}';          
        ";

        $query = $this->db->query($sql);
        $writeInfo = $query->row_array();  // 쿼리 결과를 배열로 받음
		$letterContent = explode('$#', $writeInfo['content']);  // 내용 구분자가 '$#'일 경우, 내용 분리
		$filtered = array_filter($letterContent, function($value) {
			return !preg_match('/^[\s\x{200B}\x{200C}\x{200D}\x{2060}\x{FEFF}]*$/u', $value);
		});

		$writeInfo['content'] = array_values($filtered);

        /* 심플 경우 편지지 출력 x */
        // write_list 테이블과 letter_list 테이블을 JOIN하여 letterIdx에 해당하는 cateIdx를 조회
        $sql = "SELECT
                    L.cateIdx                    
                FROM
                    write_list AS W JOIN
                    letter_list AS L ON L.idx = W.letterIdx
                WHERE
                    W.idx = '{$writeInfo['idx']}';";

        $query = $this->db->query($sql);
        $cateIdx = $query->row_array()['cateIdx'];  // 카테고리 인덱스 조회

        // cateIdx 값에 따라 간단한 편지지인지 판단 ( 1 - 기본, 2 - 심플, 38 - 게임 )
        $writeInfo['isSimple'] = ($cateIdx == 1 || $cateIdx == 2 || $cateIdx == 38); 
        $writeInfo['cateIdx'] = $cateIdx;

        // 해당 편지지의 상세 정보를 조회
        $sql = "
             SELECT
                 *
             FROM
                 letter_list
             WHERE
                 idx = '{$writeInfo['letterIdx']}';
        ";

        $query = $this->db->query($sql);
        $letterInfo = $query->row_array();  // letter_list에서 데이터 조회
        $letterInfo['thumbnail'] = json_decode($letterInfo['thumbnail'], true);  // 썸네일 데이터 JSON 디코딩
        $letterInfo['imgPath'] = '/assets/upload/'.$letterInfo['thumbnail'][0]['onebonFileName'];  // 첫 번째 썸네일 이미지 경로 설정

        // 원본 썸네일 이미지 경로 처리
        $letterInfo['thumbnailOriginal'] = json_decode($letterInfo['thumbnailOriginal'], true);
        $letterInfo['onebonImgPath'] = empty($letterInfo['thumbnailOriginal'])? '' : '/assets/upload/'.$letterInfo['thumbnailOriginal'][0]['onebonFileName'];

        // 뒷면 썸네일 이미지 경로 처리
        $letterInfo['thumbnailBack'] = json_decode($letterInfo['thumbnailBack'], true);
        if(!empty($letterInfo['thumbnailBack'])){
            $letterInfo['imgPathBack'] = '/assets/upload/'.$letterInfo['thumbnailBack'][0]['onebonFileName'];  // 뒷면 이미지 경로 설정
        }

        // 편지의 사진 정보 처리
        $writeInfo['photos'] = json_decode($writeInfo['photos'], true);

        // 보내는 주소와 받는 주소에서 우편번호 추출 (정규식을 이용해 괄호 안의 숫자만 추출)
        $pattern = '/\((\d+)\)/'; // 괄호 안의 숫자를 추출하는 정규식
        preg_match($pattern, $writeInfo['senderAddr'], $matches); // 보내는 주소에서 우편번호 추출
        $writeInfo['senderZipcode'] = $matches[1];  // 보내는 사람의 우편번호

        preg_match($pattern, $writeInfo['receiverAddr'], $matches); // 받는 주소에서 우편번호 추출
        $writeInfo['recevierZipcode'] = $matches[1];  // 받는 사람의 우편번호

        // 'normalWriteId'와 'subWriteId' 생성
        $writeInfo['normalWriteId'] = unHyphen(substr($writeInfo['regDate'], 3, 7)).
                                    $writeInfo['idx'].
                                    $writeInfo['senderName'];  // 정상 편지 ID 생성

        // 'subWriteId'는 대봉투 여부에 따라 결정
        $writeInfo['subWriteId'] = (
            ((int)$writeInfo['totalPdfFileCnt'] > 0) ||
            ((int)$writeInfo['totalLibraryFileCnt'] > 0) ||
            ((int)$writeInfo['totalLetterCnt'] + (int)$writeInfo['totalPhotoCnt'] >= 30)
        ) ? '/대봉투' : '';
        
        $writeInfo['writeId'] = $writeInfo['orderId'];  // writeId는 orderId와 동일

        // letterInfo와 writeInfo를 배열로 반환
        return array('letterInfo' => $letterInfo, 'writeInfo' => $writeInfo);
    }
    
    public function allPrintingPost() {
        $viewData = array();  // 데이터를 담을 배열 초기화
        $this->isPost = true;  // POST 요청임을 표시

        // POST 데이터로부터 writeIdxArr 받아오기
        $writeIdxArr = json_decode($_POST['writeIdxArr'], true);  // JSON 형태로 넘어온 writeIdxArr를 배열로 변환
        $color = $this->config->item('color')[$_POST['color']];  // 색상 선택 값 가져오기

        $list = array();  // 출력할 결과를 저장할 배열 초기화

        // 각 writeIdx에 대해 데이터를 조회하여 $list에 추가
        foreach ($writeIdxArr as $writeIdx) {
            // getPostInfo 메서드를 호출하여 writeIdx에 대한 정보를 가져오기
            $info = $this->getPostInfo($writeIdx);  // getPostInfo 메서드는 편지 정보와 편지지 정보를 반환

            // 결과 배열에 추가
            $list[] = array(
                'writeInfo' => $info['writeInfo'],  // writeInfo 데이터
                'letterInfo' => $info['letterInfo']  // letterInfo 데이터
            );
        }

        // 출력할 뷰에 필요한 데이터 설정
        $viewData['title'] = date('Y-m-d')."_{$color}"."_편지일괄출력";  // 출력 제목 설정
        $viewData['list'] = $list;  // 결과 목록을 뷰에 전달

        // 페이지의 헤더, 본문, 푸터를 차례로 로드
        $this->load->view('/admin/header', $viewData);  // 헤더 로드
        $this->load->view('/admin/allPrintingPost', $viewData);  // 본문(편지 출력) 로드
        $this->load->view('/admin/footer');  // 푸터 로드
    }
    
    public function postView()
    {
        $viewData = array();  // 데이터를 담을 배열 초기화

        // URI에서 writeIdx 값을 가져옴 (세 번째 세그먼트)
        $writeIdx = $this->uri->segment(3);  
        $this->isPost = true;  // POST 요청임을 표시

        // getPostInfo 메서드를 호출하여 writeIdx에 대한 정보를 가져옴
        $info = $this->getPostInfo($writeIdx);  // 'getPostInfo'는 writeIdx에 해당하는 편지 정보와 편지지 정보를 반환

        // 뷰에 필요한 데이터를 설정
        $viewData['title'] = $info['writeInfo']['orderId'].'_편지지';  // 출력할 페이지 제목 설정
        $viewData['writeInfo'] = $info['writeInfo'];  // writeInfo 데이터를 뷰에 전달
        $viewData['letterInfo'] = $info['letterInfo'];  // letterInfo 데이터를 뷰에 전달
        $viewData['isSimple'] = $info['writeInfo']['isSimple'];  // 간단한 편지지 여부 전달

        // 페이지의 헤더, 본문, 푸터를 차례로 로드
        $this->load->view('/admin/header', $viewData);  // 헤더 로드
        $this->load->view('/admin/postView', $viewData);  // 본문(편지 보기) 로드
        $this->load->view('/admin/footer');  // 푸터 로드
    }    
    
    /********** 이미지 출력 **********/
    private function getImgInfo($writeIdx) 
    {
        // 주어진 writeIdx에 해당하는 write_list 테이블에서 모든 정보를 조회
        $sql = "SELECT * FROM write_list WHERE idx = '{$writeIdx}'; ";
        
        // 쿼리 실행 및 결과를 배열로 받음
        $query = $this->db->query($sql);
        $writeInfo = $query->row_array();  // 첫 번째 결과를 배열로 반환

        // 'photos' 필드는 JSON 형태로 저장되어 있으므로 디코딩하여 배열로 변환
        $writeInfo['photos'] = json_decode($writeInfo['photos'], true);

        // 'normalWriteId'는 날짜, idx, senderName을 결합하여 생성
        $writeInfo['normalWriteId'] = unHyphen(substr($writeInfo['regDate'], 3, 7)).
                                      $writeInfo['idx'].
                                      $writeInfo['senderName'];

        // 'subWriteId'는 totalPdfFileCnt 값에 따라 결정 ('대봉투' 또는 빈 문자열)
        // $writeInfo['subWriteId'] = ((int)$writeInfo['totalPdfFileCnt'] > 0 ? '/대봉투' : '');   
        $writeInfo['subWriteId'] = (
            ((int)$writeInfo['totalPdfFileCnt'] > 0) ||
            ((int)$writeInfo['totalLibraryFileCnt'] > 0) ||
            ((int)$writeInfo['totalLetterCnt'] + (int)$writeInfo['totalPhotoCnt'] >= 30)
        ) ? '/대봉투' : '';
        

        // 'writeId'는 orderId와 동일
        $writeInfo['writeId'] = $writeInfo['orderId'];
		preg_match('/^\d+/', $writeInfo['orderId'], $matches);
		$firstOrderData = $matches[0];
		$writeInfo['printOrderId'] = substr($firstOrderData, -4);

        // writeInfo 배열 반환
        return $writeInfo;
    }
    
    // 모든 이미지 일괄 출력
    public function allPrintingImage() {
        $viewData = array();        

        // POST 데이터로부터 writeIdxArr 받아오기 (편지 목록)
        $writeIdxArr = json_decode($_POST['writeIdxArr'], true);

        $list = array(); // 결과를 저장할 배열

        // 각 writeIdx에 대해 데이터를 조회하여 $list에 추가
        foreach ($writeIdxArr as $writeIdx) {
            $info = $this->getImgInfo($writeIdx);  // getImgInfo 함수를 호출하여 이미지 정보를 가져옴

            $list[] = $info;  // 리스트에 추가
        }

        // 타이틀 설정 (유광/무광 여부를 반영)
        $viewData['title'] = '('.($_GET['isGloss'] == 'Y' ? '유광' : '무광').')'.date('Y-m-d')."_이미지일괄출력";
        $viewData['list'] = $list;  // 결과 리스트를 뷰에 전달

        // 뷰 로딩
        $this->load->view('/admin/header', $viewData);
        $this->load->view('/admin/allPrintingImage', $viewData);
        $this->load->view('/admin/footer');
    }

    // 특정 편지의 이미지 상세보기
    public function postImgView()
    {
        $viewData = array();

        // URL 세그먼트에서 writeIdx 받아오기
        $writeIdx = $this->uri->segment(3);

        // 해당 writeIdx에 대한 이미지 정보 가져오기
        $writeInfo = $this->getImgInfo($writeIdx);        
        $viewData['writeInfo'] = $writeInfo;  // 이미지 정보를 뷰에 전달
        $viewData['title'] = '('.($_GET['isGloss'] == 'Y' ? '유광' : '무광').')'.$writeInfo['orderId'].'_이미지';  // 타이틀 설정

        // 뷰 로딩
        $this->load->view('/admin/header', $viewData);
        $this->load->view('/admin/postImgView', $viewData);
        $this->load->view('/admin/footer');
    }
    
    /********** 봉투 출력 **********/
    
    // 입력된 문자열에서 알파벳, 숫자, 한글 간에 공백을 추가하는 함수
    private function addSpaces($input) {
        // 1. 알파벳과 숫자 사이에 공백 추가
        $output = preg_replace('/([A-Z])([0-9])/', '$1 $2', $input);

        // 2. 숫자와 알파벳 사이에 공백 추가
        $output = preg_replace('/([0-9])([A-Z])/', '$1 $2', $output);

        // 3. 숫자와 한글 사이에 공백 추가 (유니코드 사용)
        $output = preg_replace('/([0-9])([\p{L}])/u', '$1 $2', $output);

        // 4. 한글과 알파벳 사이에 공백 추가 (유니코드 사용)
        $output = preg_replace('/([\p{L}])([A-Z])/u', '$1 $2', $output);

        // 5. 공백을 제거하여 'L4' 형태로 다시 결합
        $output = preg_replace('/([A-Z])\s([0-9])/', '$1$2', $output);

        // 결과 반환
        return $output;
    }

    // 주소에서 '동' 또는 '호' 뒤의 숫자 제거하는 함수
    private function removeDongHo($address) {
        // 정규식을 사용하여 '숫자+동' 또는 '숫자+호' 패턴을 찾아 제거
        return preg_replace('/\d+동|\d+호/u', '', $address);
    }

    // 주소에서 '동' 또는 '호' 패턴을 추출하는 함수
    private function extractDongHo($address) {
        // 정규식을 사용하여 '숫자+동' 또는 '숫자+호' 패턴을 추출
        preg_match_all('/\d+동|\d+호/u', $address, $matches);

        // 추출된 패턴이 있으면 공백으로 구분하여 반환, 없으면 빈 문자열 반환
        return implode(' ', $matches[0]);
    }
    
    private function wrapNumbersWithSpan($number) {
        // 숫자를 문자열로 변환 후 한 글자씩 배열로 분할
        $digits = str_split((string) $number);

        // 각 숫자를 <span> 태그로 감싸기
        $wrappedDigits = array_map(fn($digit) => "<span>$digit</span>", $digits);

        // 배열을 문자열로 합치기
        return implode('', $wrappedDigits);
    }
    
    private function getSignInfo($writeIdx, $big) {
        // write_list 테이블에서 주어진 writeIdx에 해당하는 정보를 조회하는 SQL 쿼리
        $sql = "
             SELECT
                 *
             FROM
                 write_list
             WHERE
                 idx = '{$writeIdx}';             
        ";

        $query = $this->db->query($sql);
        $writeInfo = $query->row_array();  // 결과를 배열로 가져옴

        // writeInfo에서 regDate의 년월을 추출하고, writeIdx와 senderName을 결합하여 normalWriteId 생성
        $writeInfo['normalWriteId'] = unHyphen(substr($writeInfo['regDate'], 3, 7)).
                                    $writeInfo['idx'].
                                    $writeInfo['senderName'];

        // write_list와 letter_list를 JOIN하여 해당 writeIdx에 대한 cateIdx를 조회하는 SQL 쿼리
        $sql = "SELECT
                    L.cateIdx                    
                FROM
                    write_list AS W JOIN
                    letter_list AS L ON L.idx = W.letterIdx
                WHERE
                    W.idx = '{$writeInfo['idx']}';";

        $query = $this->db->query($sql);

        // cateIdx가 존재하지 않으면 기본값으로 38을 설정(게임)
        if(empty($query->row_array())) {
            $cateIdx = 38;
        } else {
            $cateIdx = $query->row_array()['cateIdx'];
        }

        // writeId 생성: orderId를 공백을 추가하여 가공하고, cateIdx에 따라 추가 정보를 결정
        $writeInfo['writeId'] = $this->addSpaces($writeInfo['orderId']).' '.
                                (($cateIdx == 1 || $cateIdx == 2) ? $writeInfo['productName'] : (($cateIdx == 38) ? '기타' : '테마'));

        // senderAddr와 receiverAddr에서 괄호 안의 숫자(우편번호)를 추출하여 처리
        $pattern = '/\((\d+)\)/';  // 괄호 안의 숫자를 추출하는 정규식
        preg_match($pattern, $writeInfo['senderAddr'], $matches);  // senderAddr에서 우편번호 추출
        $writeInfo['senderZipcode'] = $this->wrapNumbersWithSpan($matches[1]);  // 우편번호에 <span> 태그 추가
        // $writeInfo['senderAddr'] = $this->removeDongHo($writeInfo['senderAddr'].' '.$writeInfo['senderAddrDetail']);  // '동' 또는 '호'를 제거
        // $writeInfo['senderAddrDetail'] = $this->extractDongHo($writeInfo['senderAddrDetail']);  // '동' 또는 '호' 부분을 추출

        // receiverAddr도 senderAddr과 동일하게 처리
        preg_match($pattern, $writeInfo['receiverAddr'], $matches);  // receiverAddr에서 우편번호 추출
        $writeInfo['recevierZipcode'] = $this->wrapNumbersWithSpan($matches[1]);  // 우편번호에 <span> 태그 추가
        // $writeInfo['receiverAddr'] = $this->removeDongHo($writeInfo['receiverAddr'].' '.$writeInfo['receiverAddrDetail']);  // '동' 또는 '호'를 제거
        // $writeInfo['receiverAddrDetail'] = $this->extractDongHo($writeInfo['receiverAddrDetail']);  // '동' 또는 '호' 부분을 추출

        // 최종적으로 가공된 writeInfo를 반환
        return $writeInfo;
    }        
    
    public function allPrintingSign() {
        $viewData = array();        

        // 'big' 파라미터가 있으면 'big'을, 없으면 빈 문자열을 할당
        $big = empty($_GET['big']) ? '' : 'big';        

        // POST 데이터로부터 writeIdxArr 받아오기 (JSON 형식)
        $writeIdxArr = json_decode($_POST['writeIdxArr'], true);
        $list = array(); // 결과를 저장할 배열

        // 각 writeIdx에 대해 데이터를 조회하여 $list에 추가
        foreach ($writeIdxArr as $writeIdx) {
            $row = $this->db->query("SELECT totalPdfFileCnt, totalLibraryFileCnt, totalLetterCnt, totalPhotoCnt FROM write_list WHERE idx = '{$writeIdx}'")->row_array();
        
            // 대봉투 조건
            $isBig = (
                $row['totalPdfFileCnt'] > 0 ||
                $row['totalLibraryFileCnt'] > 0 ||
                ($row['totalLetterCnt'] + $row['totalPhotoCnt']) >= $this->config->item('maxBigCnt')
            );
        
            // 현재 big 출력 모드와 일치하지 않으면 스킵
            if (($big == 'big' && !$isBig) || ($big == '' && $isBig)) {
                continue;
            }
        
            $info = $this->getSignInfo($writeIdx, $big);
            $list[] = $info;
        }
        

        // 페이지 제목 설정: 날짜와 'big' 여부에 따라 제목을 결정
        $viewData['title'] = date('Y-m-d')."_".($big == 'big' ? '대' : '')."봉투일괄출력";

        // 출력할 데이터와 'big' 상태를 뷰에 전달
        $viewData['list'] = $list;
        $viewData['big'] = $big;

        // 헤더, 본문, 푸터 뷰 로딩
        $this->load->view('/admin/header', $viewData);
        $this->load->view('/admin/allPrintingSign', $viewData);
        $this->load->view('/admin/footer');
    }
    
    public function signView() {
        $viewData = array(); // 뷰에 전달할 데이터 배열 초기화

        // 'big' 파라미터가 'Y'이면 'big'을, 아니면 빈 문자열을 설정
        $big = $_GET['big'] == 'Y' ? 'big' : '';

        // URL에서 3번째 세그먼트 (writeIdx) 가져오기
        $writeIdx = $this->uri->segment(3);

        // getSignInfo 함수 호출하여 서명 정보를 가져옴
        $writeInfo = $this->getSignInfo($writeIdx, $big);

        // 페이지 제목 설정: 'orderId_대봉투' 또는 '봉투'
        $viewData['title'] = $writeInfo['orderId'] . '_' . ($big == 'big' ? '대' : '') . '봉투';

        // 조회된 서명 정보를 'data' 키로 뷰에 전달
        $viewData['data'] = $writeInfo;

        // 'big' 값을 뷰에 전달
        $viewData['big'] = $big;

        // 헤더, 본문, 푸터 뷰 로딩
        $this->load->view('/admin/header', $viewData);
        $this->load->view('/admin/signView', $viewData);
        $this->load->view('/admin/footer');
    }
    
    public function board() {
        $viewData = array(); // 뷰에 전달할 데이터 배열 초기화

        // 'dateType' 파라미터가 없으면 'WEEK'으로 설정
        $dateType = empty($_GET['dateType']) ? 'WEEK' : $_GET['dateType'];

        // 'type' 파라미터가 없으면 'qna'로 설정
        $type = $_GET['type'] ?? 'qna';

        // 'type'에 따른 SQL 조건 설정
        $searchSql = " type = '{$type}' AND ";

        // 'qna' 타입에 대한 날짜 필터링 (1주일 이내)
        if ($type == 'qna') {
            if ($dateType == 'WEEK') {
                // 1주일 전 날짜 구하기
                $oneWeekAgoTimestamp = strtotime('-1 week');
                $oneWeekAgoDate = date('Y-m-d', $oneWeekAgoTimestamp);

                // 검색 조건에 날짜 필터 추가
                $searchSql .= " date(regDate) >= '{$oneWeekAgoDate}' AND ";
            }
        }

        // SQL 쿼리 작성
        $sql = "
             SELECT
                 *
             FROM
                 board_list
             WHERE                
                $searchSql
                isUse = 'Y'
             ORDER BY
                idx DESC;
        ";

        // 쿼리 실행
        $query = $this->db->query($sql);
        $list = $query->result_array();  // 결과를 배열로 반환

        // 게시물 데이터 처리
        for ($i = 0; $i < count($list); $i++) {
            $row = $list[$i];

            // 'type'에 대한 추가 처리
            $row['type'] = $this->config->item('noticeCate')[$type];

            // 날짜를 원하는 형식으로 변환
            $row['regDate'] = substr($row['regDate'], 2, 14); // 'YY-MM-DD HH:MM:SS' 형태로 변환

            $list[$i] = $row;  // 처리된 게시물 데이터를 리스트에 업데이트
        }

        // 뷰 데이터 설정
        $viewData['title'] = $this->config->item('noticeCate')[$type]; // 게시판 제목
        $viewData['type'] = $type;  // 게시판 타입
        $viewData['dateType'] = $dateType;  // 날짜 타입
        $viewData['list'] = $list;  // 게시물 리스트

        // 뷰 로드
        $this->load->view('/admin/header');
        $this->load->view('/admin/board/list', $viewData);  // 게시물 목록 뷰
        $this->load->view('/admin/footer');
    }
    
    public function qnaView() {
        $viewData = array();  // 뷰에 전달할 데이터 배열 초기화

        // URL에서 게시물의 idx를 가져옵니다. 없으면 기본값 0을 사용.
        $idx = $this->uri->segment(3, 0);

        // 주어진 idx로 게시물 정보를 조회하는 SQL 쿼리 작성
        $sql = "
             SELECT
                 *
             FROM
                 board_list
             WHERE
                idx = '{$idx}'
        ";

        // 쿼리 실행
        $query = $this->db->query($sql);
        $info = $query->row_array();  // 게시물 정보 가져오기

        // 등록 날짜 포맷 수정 ('YY-MM-DD HH:MM:SS' -> 'YY-MM-DD HH:MM')
        $info['regDate'] = substr($info['regDate'], 2, 14);

        // 게시물이 없으면 메인 페이지로 리디렉션
        if(empty($info)){
            header('Location: /');
            exit;
        }

        // 게시물 정보를 뷰 데이터에 추가
        $viewData['info'] = $info;

        // 게시물 작성자의 이름을 초기화
        $viewData['mbInfo']['senderName'] = '';

        // 게시물 작성자가 존재하면 작성자 정보를 조회
        if(!empty($info['memberIdx'])) {
            $sql = "
                 SELECT
                     *
                 FROM
                     member_list
                 WHERE
                    idx = '{$info['memberIdx']}'
            ";

            // 작성자 정보 쿼리 실행
            $query = $this->db->query($sql);
            $memberInfo = $query->row_array();

            // 작성자 정보가 있으면 뷰 데이터에 추가
            $viewData['mbInfo'] = $memberInfo;

            // 작성자의 이름이 없으면 마지막으로 작성된 송신자 이름을 조회
            if(empty($memberInfo['senderName'])) {
                $sql = "SELECT * FROM write_list WHERE memberIdx = '{$memberInfo['idx']}' ORDER BY idx DESC LIMIT 1";

                $query = $this->db->query($sql);                                 
                $writeInfo = $query->row_array();

                // 송신자 이름이 있으면 이를 추가
                if(!empty($writeInfo)) {
                    $viewData['mbInfo']['senderName'] = $writeInfo['senderName'];
                }
            }
        }

        // 답변 정보 초기화
        $viewData['answerInfo'] = array();

        // 게시물에 답변이 있으면 답변 정보 조회
        if(!empty($info['answerIdx'])){
            $sql = "
                 SELECT
                     *
                 FROM
                     board_list
                 WHERE
                    idx = '{$info['answerIdx']}'
            ";

            // 답변 정보 쿼리 실행
            $query = $this->db->query($sql);
            $answerInfo = $query->row_array();

            // 답변 날짜 포맷 수정
            $answerInfo['regDate'] = substr($answerInfo['regDate'], 2, 14);

            // 답변 정보를 뷰 데이터에 추가
            $viewData['answerInfo'] = $answerInfo;            
        }

        // 답변이 있는지 여부 확인
        $viewData['isAnswer'] = !empty($viewData['answerInfo']);

        // 뷰 로드
        $this->load->view('/admin/header');
        $this->load->view('/admin/board/qnaView', $viewData);  // Q&A 상세 보기 뷰
        $this->load->view('/admin/footer');
    }
    
    public function noticeView() {
        $viewData = array();  // 뷰에 전달할 데이터 배열 초기화

        // URL에서 게시물의 idx를 가져옵니다. 없으면 기본값 0을 사용.
        $idx = $this->uri->segment(3, 0);

        $info = array();

        // idx가 존재하면 해당 게시물 정보를 조회
        if(!empty($idx)){
            $sql = "
                 SELECT
                     *
                 FROM
                     board_list
                 WHERE
                    idx = '{$idx}'
            ";

            // 쿼리 실행
            $query = $this->db->query($sql);
            $info = $query->row_array();  // 게시물 정보 가져오기

            // 등록 날짜 포맷 수정 ('YY-MM-DD HH:MM:SS' -> 'YY-MM-DD HH:MM')
            $info['regDate'] = substr($info['regDate'], 2, 14);   
        }                       

        // 게시물 정보를 뷰 데이터에 추가
        $viewData['info'] = $info;        

        // 게시물이 존재하면 'isCreate'를 true로 설정, 그렇지 않으면 false
        $viewData['isCreate'] = !empty($viewData['info']);

        // 뷰 로드
        $this->load->view('/admin/header');
        $this->load->view('/admin/board/noticeView', $viewData);  // 공지사항 상세 보기 뷰
        $this->load->view('/admin/footer');
    }
    
    public function popup()
    {
        $viewData = array();  // 뷰에 전달할 데이터 배열 초기화        

        // 활성화된 팝업을 조회하는 SQL 쿼리 작성
        $sql = "
             SELECT
                 *
             FROM
                 popup
             WHERE                
                isUse = 'Y'
             ORDER BY
                idx DESC;
        ";

        // 쿼리 실행
        $query = $this->db->query($sql);
        $list = $query->result_array();  // 결과를 배열로 가져옴

        // 팝업 목록을 가공하여 추가적인 정보를 생성
        for($i = 0; $i < count($list); $i++) {
            $row = $list[$i];

            // 'thumbnail' 필드는 JSON 형식으로 저장되어 있으므로 이를 배열로 디코드
            $row['thumbnail'] = json_decode($row['thumbnail'], true);

            // 'onebonImgPath'와 'resizeImgPath'를 설정
            $row['onebonImgPath'] = '/assets/upload/popup/'.$row['thumbnail'][0]['onebonFileName'];
            $row['resizeImgPath'] = '/assets/upload/popup/'.$row['thumbnail'][0]['fileName'];

            // 등록일을 'YY-MM-DD HH:MM' 형식으로 수정
            $row['regDate'] = substr($row['regDate'], 2, 14);

            // 가공된 데이터로 목록을 업데이트
            $list[$i] = $row;
        }

        // 뷰 데이터에 팝업 목록 추가
        $viewData['list'] = $list;        

        // 뷰 로드
        $this->load->view('/admin/header');
        $this->load->view('/admin/popup', $viewData);  // 팝업 리스트 뷰
        $this->load->view('/admin/footer');
    }

    public function userLog()
    {
        // 뷰에 전달할 데이터를 담을 배열 초기화
        $viewData = array();

        // 로그 타입 파라미터 추가 (기본값은 'login')
        $type = $this->input->get('type') ?? 'login';

        // 페이지 번호, 검색 유형, 검색 텍스트, SNS, 회원 상태에 대한 파라미터 초기화
        $page = empty($_GET['page']) ? 1 : $_GET['page'];
        $searchType = empty($_GET['searchType']) ? '' : $_GET['searchType'];
        $searchTxt = empty($_GET['searchTxt']) ? '' : $_GET['searchTxt'];
        $sns = empty($_GET['sns']) ? 'all' : $_GET['sns'];
        $mbState = empty($_GET['mbState']) ? 'all' : $_GET['mbState'];

        // 한 페이지에 표시할 항목 수와 페이지당 제한 설정
        $paginCount = 50;
        $limit = getPageLimit($page, $paginCount);

        // 검색 조건을 위한 SQL 부분 초기화
        $searchSql = "1=1";
        $bindValues = [];

        // 검색 유형과 검색 텍스트가 있을 경우 검색 조건에 추가
        if (!empty($searchType) && !empty($searchTxt)) {
            if ($searchType == 'idx') {
                $searchSql .= " AND {$searchType} = ?";
                $bindValues[] = $searchTxt;
            } else {
                $searchSql .= " AND {$searchType} LIKE ?";
                $bindValues[] = "%{$searchTxt}%";
            }
        }

        // SNS 필터링 조건 추가
        if ($sns != 'all') {
            $searchSql .= " AND sns = ?";
            $bindValues[] = $sns;
        }

        // 로그 타입에 따라 다른 테이블에서 데이터 가져오기
        if ($type == 'login') {
            $sql = "
                SELECT * 
                FROM user_log 
                WHERE $searchSql 
                ORDER BY idx DESC 
                LIMIT ?, ?;
            ";
        } else if ($type == 'access') {
            $sql = "
                SELECT * 
                FROM access_log 
                WHERE $searchSql 
                ORDER BY id DESC 
                LIMIT ?, ?;
            ";
        }

        // 바인딩할 값 추가 (페이징)
        $bindValues[] = (int)$limit;
        $bindValues[] = (int)$paginCount;

        // 쿼리 실행하여 로그 목록을 가져옴
        $query = $this->db->query($sql, $bindValues);
        $list = ($query && $query->num_rows() > 0) ? $query->result_array() : [];

        // 로그 목록을 순회하여 날짜 형식 등을 변경
        for ($i = 0; $i < count($list); $i++) {
            $row = $list[$i];

            // 활동 날짜를 '시간 전' 형식으로 변환
            if ($type == 'login') {
                $row['login_date'] = $this->timeAgo($row['login_date']);
            } else if ($type == 'access') {
                $row['access_date'] = $this->timeAgo($row['access_date']);
            }

            // 로그 생성을 2자리 년도 + 시간 형식으로 변환
            if (isset($row['regDate'])) {
                $row['regDate'] = substr($row['regDate'], 2, 14);
            }

            // 변환된 값을 다시 배열에 저장
            $list[$i] = $row;
        }

        // 검색 조건에 맞는 로그 수를 조회하는 SQL 쿼리
        $countSql = "
            SELECT COUNT(*) AS cnt
            FROM " . ($type == 'login' ? 'user_log' : 'access_log') . "
            WHERE $searchSql;
        ";

        // 전체 로그 수 가져오기
        $query = $this->db->query($countSql, array_slice($bindValues, 0, -2)); // LIMIT 제외한 값만 바인딩
        $countResult = $query->row_array();
        $totalCnt = isset($countResult['cnt']) ? $countResult['cnt'] : 0;

        // 페이지 데이터와 검색 조건을 뷰에 전달할 데이터로 준비
        $viewData['pageData'] = array(
            'totalCnt' => $totalCnt,
            'page' => $page,
            'pagingCount' => $paginCount
        );

        // 검색 조건 데이터를 뷰에 전달할 데이터로 준비
        $viewData['searchData'] = array(
            'searchType' => $searchType,
            'searchTxt' => $searchTxt,
            'sns' => $sns,
        );

        // 로그 목록을 뷰에 전달할 데이터로 준비
        $viewData['list'] = $list;
        $viewData['type'] = $type; // 현재 로그 타입을 뷰에 전달

        // 헤더, 본문, 푸터 뷰를 로드하여 화면에 출력
        $this->load->view('/admin/header');
        $this->load->view('/admin/userLog', $viewData);
        $this->load->view('/admin/footer');
    }

    public function mailbox()
    {
        $viewData = array();

        // 기본 검색 조건 설정
        $page = empty($_GET['page']) ? 1 : $_GET['page'];
        $searchType = empty($_GET['searchType']) ? '' : $_GET['searchType'];
        $searchTxt = empty($_GET['searchTxt']) ? '' : $_GET['searchTxt'];

        $pagingCount = 50;
        $limit = getPageLimit($page, $pagingCount);

        // // 검색 조건 SQL
        $searchSql = "1=1";
        if (!empty($searchType) && !empty($searchTxt)) {
            if ($searchType == 'memberIdx') {
                $searchSql .= " AND M.{$searchType} = '{$searchTxt}'";
			} elseif ($searchType == 'senderName') {
				$searchSql .= " AND M.nickname LIKE '%{$searchTxt}%'";
			} else {
                $searchSql .= " AND M.{$searchType} LIKE '%{$searchTxt}%'";
            }
        }

        // 메일박스 리스트 조회
        $sql = "
            SELECT 
                MB.*,
                M.*,
                MB.senderName,
                MB.scan_status,
                M.nickname,
                M.senderTel,
                MB.regDate,
                MB.receiverName,
                MB.receiverAddr,
                MB.receiverAddrDetail,
                MB.receiverTel,
                MB.idx AS mailboxIdx,
                M.idx AS memberIdx
            FROM 
                mailbox_list AS MB
            LEFT JOIN 
                member_list AS M ON MB.memberIdx = M.idx
            WHERE 
                {$searchSql}
            ORDER BY 
                MB.idx DESC
            LIMIT {$limit}, {$pagingCount};
        ";

        $query = $this->db->query($sql);
        $list = $query->result_array();

        // 날짜 포맷 처리
        foreach ($list as &$row) {
            $row['regDate'] = substr($row['regDate'], 2, 14);
            if (!empty($row['deliveryDate'])) {
                $row['deliveryDate'] = substr($row['deliveryDate'], 2, 14);
            }
        }

        // 전체 개수 조회
        $sql = "
            SELECT COUNT(*) AS cnt
            FROM mailbox_list AS MB
            LEFT JOIN member_list AS M ON MB.memberIdx = M.idx
            WHERE {$searchSql}
        ";
        $query = $this->db->query($sql);
        $totalCnt = $query->row_array()['cnt'];

        // 뷰에 전달
        $viewData['list'] = $list;
        $viewData['pageData'] = array(
            'totalCnt' => $totalCnt,
            'page' => $page,
            'pagingCount' => $pagingCount
        );
        $viewData['searchData'] = array(
            'searchType' => $searchType,
            'searchTxt' => $searchTxt
        );
        $viewData['payType'] = $this->config->item('payType');


        $this->load->view('/admin/header');
        $this->load->view('/admin/mailbox', $viewData);
        $this->load->view('/admin/footer');
    }


 
    public function logout()
    {
        session_start();
        session_destroy();
        
        header('Location: /admin/login');
    }        

    public function postToday()
    {
        // 뷰에 전달할 데이터를 담을 배열 초기화
        $viewData = array();

        // 엑셀 다운로드 여부 확인 (파라미터 isExcel이 없으면 'N'으로 기본 설정)
        $isExcel = empty($_GET['isExcel']) ? 'N' : 'Y';

        // 현재 페이지 번호 (파라미터가 없으면 기본값 1)
        $page = empty($_GET['page']) ? 1 : $_GET['page'];    

        // 검색 유형 및 검색어 (기본값은 빈 문자열)
        $searchType = empty($_GET['searchType']) ? '' : $_GET['searchType'];
        $searchTxt = empty($_GET['searchTxt']) ? '' : $_GET['searchTxt'];

        // 상태 필터링 (기본값은 'W' - 대기 상태)
        $state = empty($_GET['state']) ? 'W' : $_GET['state'];

        // 색상 필터링 (기본값 'all' - 모든 색상)
        $color = empty($_GET['color']) ? 'all' : $_GET['color'];

        // 도장 여부 필터링 (기본값 99 - 모든 경우)
        $stamp = isset($_GET['stamp']) ? $_GET['stamp'] : 99;

        // 페이지당 표시할 데이터 개수 (기본값 50개)
        $pagingCnt = empty($_GET['pagingCnt']) ? 50 : $_GET['pagingCnt'];

        // 봉투 무게
        $envelopeWeightSort = empty($_GET['envelopeWeight']) ? 'all' : $_GET['envelopeWeight'];

        // 날짜 관련 변수 설정
        $today = date('Y-m-d'); // 오늘 날짜
        $day1ago = date('Y-m-d', strtotime('-1 day', strtotime($today))); // 하루 전 날짜
        $week1ago = date('Y-m-d', strtotime('-1 week', strtotime($today))); // 일주일 전 날짜

        // 배송일자 필터링 설정
        $startDate = empty($_GET['startDate']) ? $today : $_GET['startDate']; // 기본값: 오늘
        $endDate = empty($_GET['endDate']) ? $today : $_GET['endDate']; // 기본값: 오늘
        // 배송일자 필터링 설정 (기본값: 전체 'ALL')
        $dateState = empty($_GET['dateState']) ? 'TODAY' : $_GET['dateState'];
        // 완료일자 필터링 설정
        $finishDateState = empty($_GET['finishDateState']) ? 'TODAY' : $_GET['finishDateState']; // 기본값: 전체('ALL')
        $finishStartDate = empty($_GET['finishStartDate']) ? $today : $_GET['finishStartDate']; // 기본값: 일주일 전 날짜
        $finishEndDate = empty($_GET['finishEndDate']) ? $today : $_GET['finishEndDate']; // 기본값: 오늘 날짜

        // 봉투 종류 필터링 (기본값 'all' - 모든 봉투)
        $envelope = empty($_GET['envelope']) ? 'all' : $_GET['envelope'];

        // 페이지네이션을 위한 limit 설정 (현재 페이지 및 페이지당 개수 사용)
        $limit = getPageLimit($page, $pagingCnt);

        // 검색 및 limit 관련 SQL 조건문 초기화
        $searchSql = "";
        $limitSql = "";

        // ✅ **발송일(`finishDateState`) 기준으로 조회하도록 변경**
        $searchSql .= "(DATE(W.finishDate) >= '{$finishStartDate}' AND DATE(W.finishDate) <= '{$finishEndDate}') AND ";   
        

                         
        // 색상(color) 필터링 조건 추가
        switch($color) {
            case 'black': // 검정색
                $searchSql .= " L.cateIdx = 1 AND ";
                break;
            case 'purple': // 보라색
                $searchSql .= " L.idx = 134 AND ";
                break;
            case 'grren': // (오타 가능성 있음) 초록색?
                $searchSql .= " L.idx = 133 AND ";
                break;
            case 'ivory': // 아이보리색
                $searchSql .= " L.idx = 132 AND ";
                break;
            case 'yellow': // 노란색
                $searchSql .= " L.idx = 35 AND ";
                break;
            case 'blue': // 파란색
                $searchSql .= " L.idx = 34 AND ";
                break;
            case 'pink': // 분홍색
                $searchSql .= " L.idx = 33 AND ";
                break;
            case 'tema': // 특정 카테고리(테마)
                $searchSql .= " L.cateIdx > 3 AND L.cateIdx != 38 AND ";
                break;
            case 'sdoku': // 스도쿠 관련 항목
                $searchSql .= " L.cateIdx = 38 AND L.name LIKE '%스도쿠%' AND ";
                break;
            case 'difference': // 숨은그림찾기 관련 항목
                $searchSql .= " L.cateIdx = 38 AND L.name LIKE '%숨은그림찾기%' AND ";
                break;
        }

        // 도장(stamp) 필터링 적용 (기본값 99는 모든 경우를 포함)
        if($stamp != 99) {
            $searchSql .= " W.stamp = '{$stamp}' AND ";
        }

        // 검색 조건 (searchType 및 searchTxt가 비어있지 않은 경우)
        if(!empty($searchType) && !empty($searchTxt)) {
            if($searchType == 'W.memberIdx') { // 회원 인덱스로 검색하는 경우 (정확한 매칭 필요)
                $searchSql .= "{$searchType} = '{$searchTxt}' AND ";
            } else { // 그 외 검색어가 포함된 경우 (LIKE 검색 적용)
                $searchSql .= "{$searchType} LIKE '%{$searchTxt}%' AND ";
            }
        }

        // 최대 편지 개수 설정값 가져오기
        $maxBigCnt = $this->config->item('maxBigCnt');

        // 봉투 크기(envelope) 필터링 적용
        switch($envelope) {
            case 'normal': // 일반 크기의 편지 (PDF 파일이 없거나 총 개수가 maxBigCnt 미만인 경우)
                $searchSql .= " W.totalPdfFileCnt = 0 AND W.totalLibraryFileCnt = 0 AND (W.totalLetterCnt + W.totalPhotoCnt) < {$maxBigCnt} AND ";
                break;
            case 'big': // 대형 봉투 (PDF 파일이 포함되거나, 편지+사진 개수가 maxBigCnt 이상인 경우)
                $searchSql .= " (W.totalPdfFileCnt > 0 OR W.totalLibraryFileCnt > 0 OR (W.totalLetterCnt + W.totalPhotoCnt) >= {$maxBigCnt}) AND ";
                break;
        }

        // 전체 보기(isAllView) 여부 설정
        // 엑셀 다운로드가 아닌 경우 && 특정 상태(W, I, P)가 아닌 경우 isAllView = 'N'
        $isAllView = ($isExcel == 'N' && $state != 'W' && $state != 'R' && $state != 'S' && $state != 'I' && $state != 'P') ? 'N' : 'Y';

        // 기본 정렬 순서
        $ORDER = 'ASC';

        // 전체 보기가 아닌 경우 (즉, 특정 필터링이 적용된 경우)
        if($isAllView == 'N') {
            $limitSql = " LIMIT $limit, $pagingCnt "; // 페이지네이션 적용
            $ORDER = 'DESC'; // 정렬을 내림차순으로 변경
        }

        // 엑셀 다운로드가 요청된 경우, 정렬 순서를 내림차순으로 변경
        if($isExcel == 'Y') {
            $ORDER = 'DESC';
        }
        
        // SQL 쿼리 작성: 글 목록 조회
        $sql = "
            SELECT
                W.*,
                L.cateIdx,
                L.name,                         
                CASE 
                    WHEN W.totalPdfFileCnt > 0 OR W.totalLibraryFileCnt > 0 OR (W.totalLetterCnt + W.totalPhotoCnt) >= {$maxBigCnt} 
                      OR W.idx IN ('8464') -- 추후 삭제 필요
                    THEN 1 
                    ELSE 0 
                END AS isBig
            FROM
                write_list AS W 
                LEFT JOIN letter_list AS L ON L.idx = W.letterIdx
            WHERE
                $searchSql
                W.isUse = 'Y'
                AND W.state = 'F'          
            ORDER BY
                W.idx $ORDER
            
        ";        

        // 쿼리 실행
        $query = $this->db->query($sql);
        $list = $query->result_array();

        // 데이터 후처리 (반복문을 통해 각 row에 추가적인 데이터를 설정)
        for($i = 0; $i < count($list); $i++) {
            $row = $list[$i];

            // writeId를 orderId 값으로 설정
            $row['writeId'] = $row['orderId'];

            // normalWriteId 생성 (등록 날짜 일부 + idx + 발신자명)
            $row['normalWriteId'] = unHyphen(substr($row['regDate'], 3, 7)) .
                                    $row['idx'] .
                                    $row['senderName'];

            // 날짜 형식 변경 (finishDate: YYMMDD 형식, regDate: YYMMDDHHMMSS 형식)
            $row['finishDate'] = substr($row['finishDate'], 2, 8);
            $row['regDate'] = substr($row['regDate'], 2, 14);

            // 사진 데이터 JSON 디코딩
            $row['photos'] = json_decode($row['photos'], true);
            $row['isGlossCnt'] = 0;
            $row['isNoneGlossCnt'] = 0;

            // 각 사진에 대해 광택 여부 카운트
            foreach ($row['photos'] as $key => $img) {
                if (empty($img['isGloss']) || $img['isGloss'] == 'Y') {
                    $row['isGlossCnt']++;
                } else {
                    $row['isNoneGlossCnt']++;
                }
            }

            $list[$i] = $row;
        }

        // 엑셀 다운로드가 아닌 경우 추가적인 데이터 조회 및 화면 출력
        if ($isExcel == 'N') {
            // 총 데이터 개수 조회 쿼리 실행
            $sql = "
                SELECT
                    COUNT(*) AS cnt    
                FROM
                    write_list AS W 
                    LEFT JOIN letter_list AS L ON L.idx = W.letterIdx
                WHERE
                    $searchSql
                    W.isUse = 'Y';
            ";

            $query = $this->db->query($sql);
            $totalCnt = $query->row_array()['cnt'];

            // 페이지네이션 데이터 설정
            $viewData['pageData'] = array(
                'totalCnt' => $totalCnt,
                'page' => $page,
                'pagingCount' => $pagingCnt
            );

            // 검색 필터 데이터 설정
            $viewData['searchData'] = array(
                'searchType' => $searchType,
                'searchTxt' => $searchTxt,
                'state' => $state,
                'dateState' => $dateState,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'finishDateState' => $finishDateState,
                'finishStartDate' => $finishStartDate,
                'finishEndDate' => $finishEndDate,
                'color' => $color,
                'stamp' => $stamp,
                'envelope' => $envelope,
                'pagingCnt' => $pagingCnt,
                'envelopeWeight' => $envelopeWeightSort,
            );

            // 리스트 데이터 설정
            $viewData['list'] = $list;                
            $viewData['isAllView'] = $isAllView;

            // 결제는 됐지만 대기 상태로 변경되지 않은 주문 확인
            checkPaymentOrder(0);

            // 관리자 페이지 뷰 로드
            $this->load->view('/admin/header');
            $this->load->view('/admin/post', $viewData);
            $this->load->view('/admin/footer');
        } else {            
            // 엑셀 다운로드 요청인 경우 JSON 데이터 반환
            $result['list'] = $list;
            echo json_encode($result);
        }
    }
}
