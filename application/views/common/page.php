<!-- 
    totalCnt: 전체 데이터의 개수
    page: 현재 페이지 번호
    pagingCount: 페이지당 표시할 항목 수
-->

<?php
    // 총 페이지 수 계산
    $maxPage = getMaxPage($totalCnt, $pagingCount);
    // 이전 페이지 번호 계산
    $prevPage = getPrevPage($page);
    // 다음 페이지 번호 계산
    $nextPage = getNextPage($page, $maxPage);
    // 현재 페이지를 기준으로 표시할 페이지 배열 계산
    $pageArr = getCenterPage($page, $maxPage);        
    // 현재 URL의 쿼리 문자열을 정리하여 가져옴
    $queryString = getQueryString($_SERVER['QUERY_STRING']);
    // 페이지와 쿼리 문자열을 결합하여 전체 URL을 생성
    $combinPage = combinePage($page, $queryString);
?>

<!-- 페이지 네비게이션을 위한 HTML 구조 -->
<div id="page">
    <!-- 첫 번째 페이지가 아니면 이전 페이지로 이동할 링크를 표시 -->
    <?php if ($page != 1) { ?>
        <a href="<?=combinePage($prevPage, $queryString)?>" class="link">«</a>
    <?php } ?>
    
    <!-- 현재 페이지와 연관된 페이지 링크들 생성 -->
    <?php foreach ($pageArr as $key => $fomatPage) { ?>
        <!-- 각 페이지 번호를 링크로 표시하고, 현재 페이지에 대해서는 'active' 클래스 추가 -->
        <a href="<?=combinePage($fomatPage, $queryString)?>" class="link <?=$fomatPage == $page ? 'active' : ''?>">
            <?=$fomatPage?>
        </a>
    <?php } ?>
    
    <!-- 마지막 페이지가 아니면 다음 페이지로 이동할 링크를 표시 -->
    <?php if ($page != $maxPage) { ?>
        <a href="<?=combinePage($maxPage, $queryString)?>" class="link">»</a>
    <?php } ?>
</div>
