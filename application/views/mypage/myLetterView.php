<style>
    #ft_menu{
        display: none;
    }
    
    .link {
        display: block; margin-bottom: 10px; text-align: left; padding-bottom: 5px; border-bottom: 1px solid #b9b9b9; text-decoration: underline;   
    }     

</style>

<div id="letter">
    <?
        $cate = ['letter' => '편지지', 'image' => '이미지', 'file' => '파일'];
    ?>
    <div class="cateBox" style="margin-top: 12px; text-align: center;">
        <? foreach($cate as $key => $name) { ?>
            <button onclick="changeCate('<?=$key?>');" class="cate <?=$key?> <?=$key == 'letter'? 'active': ''?>"><?=$name?></button>
        <? } ?>
    </div>
    
    <div class="viewBox letter">        
        <? if(!$writeInfo['totalLetterCnt']) { ?>        
            <p class="empty">작성된 내용이 없어요!</p>
        <? } ?>                
        
        <? foreach($writeInfo['content'] as $key => $content){ 
            if(empty($content)) continue;
        ?>        
            <div class="letterBox" style="<?=setLetterInfo2($letterInfo, $writeInfo, 'box', false)?>">
                <textarea class="letterContent" 
                          style="<?=setLetterInfo2($letterInfo, $writeInfo, 'content', false)?>"
                          readonly><?=$content?></textarea>
            </div>        
        <? } ?>
    </div>
    
    <div class="viewBox image hide">
        <? if(!count($writeInfo['photos'])) { ?>
            <p class="empty">등록된 이미지가 없어요!</p>
        <? } ?>
        <? foreach($writeInfo['photos'] as $key => $img){ ?>        
            <div class="letterImgWrap" style="text-align:center; margin-bottom: 10px;">
                <img class="postImg" src="/assets/upload/photos/<?=$img['fileName']?>" style="max-width: 100%;"/>
            </div>
        <? } ?>
    </div>
    
    <div class="viewBox file hide">
        <? if(!count($writeInfo['pdfFiles'])) { ?>
            <p class="empty">등록된 파일이 없어요!</p>
        <? } ?>
        
        <? foreach($writeInfo['pdfFiles'] as $key => $file){                     
            $filePath = '/assets/upload/files/'.$file['fileName'];            
        ?>
            <div class="pdfFileWrap" style="text-align:center; margin-bottom: 10px;">                
                <? if(strtolower($file['ext']) != 'pdf') { ?>
                    <a <?=!isWebView()? "href='{$filePath}' download" : ''?>
                       style="display:block; margin-bottom: 10px; text-align: left; padding-bottom: 5px; border-bottom: 1px solid #b9b9b9;">
                        <i class="fas fa-solid fa-file"></i> - (<?=$writeInfo['fileColor'][$key]?>)<?=$file['originalFileName']?>
                    </a>
                    <img class="postImg" src="<?=$filePath?>?v=20250209" style="max-width: 100%;"/>                    
                <? }else { ?>                
                    <? if(isAndroid()) { ?>
                        <a class="link">
                            <i class="fas fa-solid fa-file"></i> - (<?=$writeInfo['fileColor'][$key]?>)<?=$file['originalFileName']?>
                        </a>
                        <iframe src="https://docs.google.com/gview?embedded=true&url=https://dongl.co.kr<?=$filePath?>" type="application/pdf" aria-label="example" width="100%" height="350"></iframe>
                    <? }else { ?>
                        <a class="link" href="javascript:nav.locationHref('<?=$filePath?>', 'b5');">
                            <i class="fas fa-solid fa-file"></i> - (<?=$writeInfo['fileColor'][$key]?>)<?=$file['originalFileName']?>
                        </a>
                        <embed src="<?=$filePath?>" type="application/pdf" aria-label="example" width="100%" height="500">
                    <? } ?>                              
                <? } ?>
            </div>
        <? } ?>
    </div>
</div>

<script>
    // 카테고리 변경 함수
    function changeCate(key) {
        // 모든 카테고리에서 'active' 클래스를 제거
        $(`.cate`).removeClass('active');

        // 선택된 카테고리(key)에 'active' 클래스 추가
        $(`.cate.${key}`).addClass('active');

        // 모든 뷰 박스를 숨김 처리
        $(`.viewBox`).addClass('hide');

        // 선택된 카테고리와 일치하는 뷰 박스를 숨김 처리 해제
        $(`.viewBox.${key}`).removeClass('hide');
    }

    // 페이지 로드 시 실행될 함수
    window.onload = function() {
        // viewport 메타 태그의 속성 설정 (모바일 최적화 설정)
        document.getElementById('viewport').setAttribute('content', 'width=device-width, initial-scale=1.0, user-scalable=1, maximum-scale=3');       
    };
</script>