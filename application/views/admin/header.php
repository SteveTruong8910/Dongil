<!DOCTYPE html>
<html id="html">

<head>
	<!-- 페이지 제목 설정, 동적 제목 처리 -->
	<title><?=!empty($title)? $title : '동글 - 관리자'?></title>
	
	<!-- 메타 태그 설정 (뷰포트 및 Open Graph 설정) -->
	<meta charset="utf-8">
	<meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1,width=device-width">
    <meta property="og:title" content="괸리자 - 편지지">
    <meta property="og:description" content="괸리자 - 편지지">

	<!-- CSS 파일 링크 (버전 처리된 CSS 파일) -->
	<link rel="stylesheet" type="text/css" href="/assets/css/reset.css<?=$this->config->item('ver')?>">
	<link rel="stylesheet" type="text/css" href="/assets/css/animation.css<?=$this->config->item('ver')?>">
	<link rel="stylesheet" type="text/css" href="/assets/css/admin.css<?=$this->config->item('ver')?>">
    <link rel="stylesheet" type="text/css" href="/assets/css/letter.css<?=$this->config->item('ver')?>">
    <link rel="stylesheet" type="text/css" href="/assets/css/fonts.css<?=$this->config->item('ver')?>"/>	
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.2/css/all.min.css<?=$this->config->item('ver')?>"/>
	<link rel="stylesheet" type="text/css" href="/assets/css/bootstrap-3.3.2.min.css"/>	
    <link rel="stylesheet" href="/assets/fonts/Pretendard-Regular.css">
    
    <!-- 동적으로 폰트 패밀리 추가 (글 작성 정보에 따라) -->
    <? if($this->isPost){
        if(!empty($list)) {
            foreach($list as $item) {    
                echo "<link rel='stylesheet' href='/assets/fonts/{$item['writeInfo']['fontFamily']}.css'>";
            }
        }else {
            echo "<link rel='stylesheet' href='/assets/fonts/{$writeInfo['fontFamily']}.css'>";
        }
    }
    ?>
    
	<!-- 자바스크립트 파일 링크 -->
	<script type="text/javascript" src="/assets/js/jquery-3.3.1.min.js"></script>
	<script type="text/javascript" src="/assets/js/bootstrap-3.3.2.min.js"></script>    
	<script type="text/javascript" src="/assets/js/sweetalert2.all.min.js<?=$this->config->item('ver')?>"></script>	
	<script type="text/javascript" src="/assets/js/util.js<?=$this->config->item('ver')?>"></script>
    <script type="text/javascript" src="/assets/js/admin.js<?=$this->config->item('ver')?>"></script>   
    <script type="text/javascript" src="/assets/js/pdfjs/jspdf.js"></script>
    <script type="text/javascript" src="/assets/js/pdfjs/html2canvas.js"></script>
</head>
    
<body>
    <!-- 우편번호 검색 레이어 -->
    <div id="layer" class="post_wrap" style="display: none;">
        <div class="hd">
            <p>우편번호 검색</p>
            <!-- 우편번호 레이어 닫기 버튼 -->
            <a id="btnCloseLayer" class="btn_close" onclick="closeDaumPostcode()" alt="닫기 버튼"><i class="fas fa-times"></i></a>
        </div>
    </div>
    
    <!-- 로그인 페이지가 아닌 경우, 관리자 페이지 레이아웃 시작 -->
    <? if($this->path != '/admin/login'){ ?>
        <div id="body">
            <div id="category">
                <div class="logoBox">
                    <!-- 관리자 대시보드 로고 링크 -->
                    <a href="/admin">
<!--                        <img id="logo" class="logo" src="/assets/image/white_logo.png">                -->
                        동글 - 관리자
                    </a>
                </div>
                <div class="contentBox">
                    <!-- 로그아웃 버튼 -->
                    <a class="btnLogout" href="/admin/logout">
                        <i class="fas fa-sign-out-alt"></i>
                        로그아웃
                    </a>                       
                    <!-- 관리자 대시보드 메뉴들 -->
                    <a href="/admin/post">
                        <i class="fas fa-truck"></i>
                        주문관리
                    </a>              
                    <a href="/admin/mailbox">
                        <i class="fas fa-archive"></i>
                        사서함관리
                    </a>                                      
                    <a href="/admin/letter">
                        <i class="fas fa-envelope"></i>
                        편지지관리
                    </a>          
                    <a href="/admin/library">
                        <i class="fas fa-file-alt"></i>
                        자료실관리
                    </a>          
                    <a href="/admin/member">
                        <i class="fas fa-user"></i>
                        회원관리
                    </a>                                      
                    <a href="/admin/point">
                        <i class="fas fa-coins"></i>
                        포인트관리
                        <!-- 포인트 대기 목록이 있을 경우 카운트 표시 -->
                        <? 
                            $sql = "SELECT COUNT(*) AS cnt FROM point_log WHERE isWait = 'Y' AND isUse = 'Y';";
                            $query = $this->db->query($sql);
                            $waitPointCnt = $query->row_array()['cnt'];
                        ?>
                        <? if($waitPointCnt > 0) { ?>
                            <span class="circle"><?=$waitPointCnt?></span>
                        <? } ?>
                    </a>              
                    <a href="/admin/board">
                        <i class="fas fa-bullhorn"></i>
                        게시판관리
                    </a>
                    <a href="/admin/popup">
                        <i class="fas fa-flag"></i>
                        팝업관리
                    </a>
                    <a href="/admin/userLog">
                      <i class="fas fa-file-alt"></i>
                        로그확인
                    </a>                                                 
                </div>
            </div>
            <div id="container">
    <? } ?>