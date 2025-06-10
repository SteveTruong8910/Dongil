<style>
    #category{ display: none; }
    #container{ padding: 0px; }    
</style>

<div class="a4sheet">
    <div id="postView">
        <?php 
        // 글 내용 배열을 반복하며 출력
        foreach($writeInfo['content'] as $key => $content){ 
            if(empty($content)) continue; // 내용이 비어있으면 스킵
            
            // 글의 스타일 타입 설정 (간단 모드일 경우 'simple', 아니면 'tema')
            $clsType = $isSimple ? 'simple' : 'tema';
        ?>
        
        <!-- 프린트할 때 적용될 스타일 설정 -->
        <style>
            @media print {
                #postView .letterContent<?=$writeInfo['idx']?> { 
                    color: <?=$writeInfo['color']?> !important; /* 인쇄 시 글자 색상 적용 */
                }
            }
        </style>

        <!-- 편지지 스타일 적용된 래퍼 -->
        <div class="letterWrap <?=$isSimple ? 'noLettersheet' : 'lettersheet'?>">
            <div class="letterContainer <?=$clsType?>">                                                            

                <!-- 편지 내용 박스 -->
                <div class="letterBox <?=$clsType?>" style="<?=setLetterInfo3($letterInfo, $writeInfo, 'box', $isSimple)?>">

                    <?php 
                    // 최대 줄 수만큼 반복하여 라인 생성 (단, 간단 모드일 경우에만 실행)
                    for($i = 1; $i <= $letterInfo['maxLine']; $i++){
                        if(!$isSimple) break; // 간단 모드가 아닐 경우 루프 종료
                        $lineHeight = $letterInfo['contextLineHeight'] * $i + 23; // 줄 간격 조정 (오차 보정값: 22)
                    ?>
                        <span class="line" style="width: <?=$letterInfo['contextWidth']?>px; 
                                                  top: calc(<?=$isSimple ? 0 : $letterInfo['topPadding']?>px + <?=$lineHeight?>px);">
                        </span>
                    <?php } ?>

                    <!-- 편지 내용을 포함한 텍스트 영역 (읽기 전용) -->
                    <textarea class="letterContent letterContent<?=$writeInfo['idx']?> <?=$clsType?>" 
                              style="<?=setLetterInfo3($letterInfo, $writeInfo, 'content')?>" readonly><?=$content?></textarea>
                </div>

                <!-- 작성자 ID 및 로고 영역 -->
                <div class="writeId <?=$writeInfo['isSimple'] ? 'simple' : 'hide' ?> letterLogo">
                    <?php if($writeInfo['cateIdx'] == 1 || $writeInfo['cateIdx'] == 38) { ?>
                        <img src="/assets/image/gold_logo.png"/> <!-- 특정 카테고리(일반, 게임)일 경우 골드 로고 -->
                    <?php } else { ?>
                        <img src="/assets/image/gray_logo.png"/> <!-- 나머지는 회색 로고 -->
                    <?php } ?>
                </div>

                <!-- 페이지 번호 표시 -->
                <div class="writeId <?=$isSimple ? 'simple' : 'tema' ?> index">
                    -<?=($key + 1)?>-
                </div>

                <!-- 작성자 ID 표시 -->
                <div class="writeId <?=$isSimple ? 'simple' : ''?>">
                    <?=$writeInfo['normalWriteId'].'L'.($key + 1)?><?=$writeInfo['subWriteId']?>
                </div>
            </div>
        </div>
        
        <!-- 테마의 경우 뒷면 출력 -->
        <?php if(!empty($letterInfo['imgPathBack']) && !$isSimple) { ?> 
            <div class="lettersheet tema">
                <div class="letterBox tema" style="background-image: url(<?=$letterInfo['imgPathBack']?>) !important;"></div>
            </div>
        <?php } ?>
        
        <?php } // foreach 종료 ?>
    </div>
</div>


<script>
    var initBodyHtml = '';
    var clsType = '<?=$clsType?>';

    function defaultSetup() {
        window.onbeforeprint = beforePrintSetup;
        window.onafterprint = afterPrintSetup;
        printPage();
    }    

    function setLetterFont() {
        let $letterContent = $('.letterContent');
        let minFontSize = Number.POSITIVE_INFINITY;

        // 각 textarea의 폰트 크기를 줄입니다.
        for (let i = 0; i < $letterContent.length; i++) {
            let textarea = $letterContent.eq(i),
                maxHeight = Math.ceil(textarea.height() + 1); // 오차범위 1

            let fontSize = parseFloat(textarea.css('font-size'));
            
            console.log(textarea[0].scrollHeight);
            console.log(maxHeight);
            while (textarea[0].scrollHeight > maxHeight && fontSize > 0) {                   
                console.log(fontSize);
                textarea.css('font-size', fontSize + 'px');                
                fontSize -= 0.1;
            }
        }        
    }

    function printPage() {
        window.print();
    }

    function beforePrintSetup() {
    }

    function afterPrintSetup() {
        window.opener.postMessage('downloadComplete', window.location.origin);
        window.close();
    }

    window.onload = function() {
        showLoadingBar();        
        
        document.fonts.ready.then(function() {
            setTimeout(function() {
                setLetterFont();
                hideLoadingBar();
                
                defaultSetup();                
            }, 100);
        });        
    };
</script>