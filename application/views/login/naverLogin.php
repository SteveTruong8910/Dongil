<?
    $client_id = "tDVeMCohzRzopuN8rrCQ";
    $redirect_uri = urlencode("https://dongl.co.kr/login/naverLoginCallback");
    $state = md5(uniqid(rand(), TRUE)); // CSRF 공격을 방지하기 위한 상태 코드    
    
    $naverLoginUrl = "https://nid.naver.com/oauth2.0/authorize?response_type=code&client_id={$client_id}&redirect_uri={$redirect_uri}&state={$state}";
    header("Location: $naverLoginUrl");
?>