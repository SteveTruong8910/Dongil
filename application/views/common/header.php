<!DOCTYPE html>
<html>

<head>
	<!-- 페이지 제목 설정 -->
	<title>동글</title>
	
	<!-- 문자 인코딩 및 뷰포트 설정 -->
	<meta charset="UTF-8">
	<meta id="viewport" name="viewport" content="initial-scale=1.0,user-scalable=0,maximum-scale=1,width=device-width">
    <meta name="description" content="인터넷서신 폐지? 동글 어플로 간편한 우체국 편지 예쁜 편지지로 지금 바로 마음을 전하세요.">
    
    <!-- 구글 및 네이버 사이트 인증 메타 태그 -->
    <meta name="google-site-verification" content="google4ab0a2b866fc0f4c" />
    <meta name="naver-site-verification" content="naver2015bec30f94e876365311277e244361.html" />
    
	<!-- CSS 파일 링크 (버전 처리된 CSS 파일) -->
	<link rel="stylesheet" type="text/css" href="/assets/css/reset.css<?=$this->config->item('ver')?>">
	<link rel="stylesheet" type="text/css" href="/assets/css/animation.css<?=$this->config->item('ver')?>">
	<link rel="stylesheet" type="text/css" href="/assets/css/common.css<?=$this->config->item('ver')?>">
    <link rel="stylesheet" type="text/css" href="/assets/css/letter.css<?=$this->config->item('ver')?>">
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.2/css/all.min.css<?=$this->config->item('ver')?>"/>
	<link rel="stylesheet" type="text/css" href="/assets/css/bootstrap-3.3.2.min.css<?=$this->config->item('ver')?>"/>
    
    <!-- 글자체 설정: 마이페이지 또는 편지 페이지에 맞는 폰트 적용 -->
    <? if($this->uri->segment(1, '') == 'mypage' && $this->uri->segment(2, '') == 'myLetterView') { ?>
        <link rel="stylesheet" href="/assets/fonts/<?=$writeInfo['fontFamily']?>.css?v=20250220">
    <? } ?>
        
    <? if($this->uri->segment(1, '') == 'letter' && $this->uri->segment(2, '') == '') { ?>
        <link rel="stylesheet" data-font href="/assets/fonts/<?=$this->user['fontFamily']?>.css<?=$this->config->item('ver')?>">
    <? } ?>
    
	<!-- 자바스크립트 파일 링크 -->
	<script type="text/javascript" src="/assets/js/jquery-3.3.1.min.js"></script>
	<script type="text/javascript" src="/assets/js/bootstrap-3.3.2.min.js"></script>	
	<script type="text/javascript" src="/assets/js/sweetalert2.all.min.js<?=$this->config->item('ver')?>"></script>
    <script type="text/javascript" src="/assets/js/navigation.js<?=$this->config->item('ver')?>"></script>
	<script type="text/javascript" src="/assets/js/common.js<?=$this->config->item('ver')?>"></script>
	<script type="text/javascript" src="/assets/js/util.js<?=$this->config->item('ver')?>"></script>    
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
    
    <!-- 현재 컨트롤러와 메소드 값 가져오기 -->
    <? 
        $current_controller = $this->router->fetch_class();
        $current_method = $this->router->fetch_method();                  
    ?>
    
    <!-- 헤더 영역 -->
    <header id="hd">
        <div id="hd_wrapper">
            <div class="row">
                <!-- 홈 페이지가 아닌 경우에만 헤더에 뒤로가기, 로고, 네비게이션 버튼 표시 -->
                <? if(!($current_controller == 'home' && $current_method == 'index')){ ?>                                 
                    <div class="col-xs-4">
                        <div id="hd_back" class="hd_icon">
                            <!-- 뒤로가기 버튼 (편지 페이지는 popState 사용) -->
                            <a href="javascript:void(0);" onclick="<?=($current_controller == 'letter')? 'handlePopState()' : 'nav.goBack()' ?>">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>         
                    </div>
                    <div class="col-xs-4" style="padding:0;">
                        <!-- 로고 -->
                        <a id="logo">                            
                            <?=$title?>
                        </a>
                    </div>
                    <div class="col-xs-4">
                        <div id="hd_nb" class="hd_icon">
                            <!-- 알림 아이콘 또는 홈 버튼 -->
                            <? if($current_controller == 'notice2'){ ?>
                                <a>
                                    <i class="fas fa-search"></i>
                                </a>
                            <? }else{ ?>
                                <a href="/" class="home">
                                    <img src="/assets/image/ico/ico_home.png"/>
                                </a>                                                          
                            <? } ?>
                        </div>
                    </div>
                <? }else{ // 홈 페이지 일 때 ?>
                    <div class="col-xs-4" style="display: flex; align-items: center;">
                        <!-- 포인트 충전 링크 (로그인한 사용자만 표시) -->
                        <? if(!empty($this->user)){ ?>
                            <a href="/mypage/point" class="point" style="display: flex; align-items: center; position:relative">
                                <img src="/assets/image/ico/ico_coin.png" style="width: 20px; margin-right: 5px;"/>
                                <?=number_format($this->user['point'])?>
                            </a>
                        <? } ?>
                    </div>
                    <div class="col-xs-4">
                        <!-- 로고 -->
                        <a id="logo">                            
                            <img class="logo" src="/assets/image/logo.png?v=1">
                        </a>          
                    </div>
                    <div class="col-xs-4" style="position: relative;">                        
                        <!-- 카카오 상담 버튼 -->
                        <a href="http://pf.kakao.com/_AxlNCn" target="_blank" style="position: absolute; right: 15px; top: 0px;">
                            <img src="/assets/image/ico/ico_counsel.png" width="35">
                        </a>                        
                    </div>
                <? } ?>
            </div>
        </div>
    </header>

    <!-- 메인 콘텐츠 랩 -->
	<div class="mainWrap">