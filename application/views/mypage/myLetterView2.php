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
        
        <? if(isIos()) { ?>
            <p class="letterMent">- 기존 얇게/굵게에 간격 문제가 발생하여 기존에 작성하신 편지내용과 간격차이가 있을 수 있습니다 이용에 불편을 드려죄송합니다.(개선완료)</p>
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
                    <p style="margin-bottom: 10px; text-align: left; padding-bottom: 5px; border-bottom: 1px solid #b9b9b9;">
                        <i class="fas fa-solid fa-file"></i> - (<?=$writeInfo['fileColor'][$key]?>)<?=$file['originalFileName']?>
                    </p>
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
    function changeCate(key) {
        
        $(`.cate`).removeClass('active');
        $(`.cate.${key}`).addClass('active');
        $(`.viewBox`).addClass('hide');
        $(`.viewBox.${key}`).removeClass('hide');        
    }
    
    function setLetterFont() {
        let $letterContent = $('.letterContent');                

        // 각 textarea의 폰트 크기를 줄입니다.
        for (let i = 0; i < $letterContent.length; i++) {
            let textarea = $letterContent.eq(i),
                maxHeight = textarea.height() + 1;
                        
            // font-size를 줄이기 전에 현재 글꼴 크기와 너비를 가져옵니다.
            let fontSize = parseFloat(textarea.css('font-size'));

            alert(textarea[0].scrollHeight);
            alert(maxHeight);
            // scrollHeight가 maxHeight를 초과할 때까지 font-size를 조정하고 너비를 늘립니다.
            while (textarea[0].scrollHeight > maxHeight && fontSize >= 10) {                
                fontSize = Math.max(fontSize - 0.1, 9); // 최소 font-size 10px 제한
                textarea.css('font-size', fontSize);
            }
        }
    }
    
    window.onload = function() {
        document.getElementById('viewport').setAttribute('content', 'width=device-width, initial-scale=1.0, user-scalable=1, maximum-scale=3');
        
        showLoadingBar();
        document.fonts.ready.then(function() {
            setTimeout(function() {                
                setLetterFont();
                hideLoadingBar();                  
            }, 100);
        });        
    };
</script>