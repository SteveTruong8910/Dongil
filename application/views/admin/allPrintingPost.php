<style>
    #category{ display: none; }
    #container{ padding: 0px; }
</style>

<div class="a4sheet">
    <div id="postView">
        <? foreach ($list as $item) { 
            $writeInfo = $item['writeInfo'];
            $letterInfo = $item['letterInfo'];
        ?>
            <? foreach ($writeInfo['content'] as $key => $content) { 
                if (empty($content)) continue;

                $clsType = $writeInfo['isSimple'] ? 'simple' : 'tema';
            ?>
                <style>
                    @media print {
                        #postView .letterContent<?=$writeInfo['idx']?> { 
                            color: <?=$writeInfo['color']?> !important;
                        }
                    }
                </style>
                <div class="letterWrap <?=$writeInfo['isSimple'] ? 'noLettersheet' : 'lettersheet' ?>">
                    <div class="letterContainer <?= $clsType ?>">
                        <div class="letterBox <?= $clsType ?>" style="<?= setLetterInfo3($letterInfo, $writeInfo, 'box', $writeInfo['isSimple']) ?>">

                            <? 
                            for ($i = 1; $i <= $letterInfo['maxLine']; $i++) {
                                if (!$writeInfo['isSimple']) break;
                                $lineHeight = $letterInfo['contextLineHeight'] * $i + 23; /* 오차범위 23 */
                            ?>
                                <span class="line" style="width: <?= $letterInfo['contextWidth'] ?>px; 
                                                        top: calc(<?= $writeInfo['isSimple'] ? 0 : $letterInfo['topPadding'] ?>px + <?= $lineHeight ?>px);">
                                </span>
                            <? } ?>
                            <textarea class="letterContent letterContent<?=$writeInfo['idx']?> <?=$clsType?>" data-idx="<?=$writeInfo['idx']?>" style="<?= setLetterInfo3($letterInfo, $writeInfo, 'content') ?>" readonly><?=$content?></textarea>
                        </div>
                        <div class="writeId <?=$writeInfo['isSimple'] ? 'simple' : 'hide' ?> letterLogo">
                            <? if($writeInfo['cateIdx'] == 1 || $writeInfo['cateIdx'] == 38) { ?>
                                <img src="/assets/image/gold_logo.png"/>
                            <? }else { ?>
                                <img src="/assets/image/gray_logo.png"/>
                            <? } ?>
                        </div>
                        <div class="writeId <?= $writeInfo['isSimple'] ? 'simple' : 'tema' ?> index">-<?=($key + 1)?>-</div>
                        <div class="writeId <?= $writeInfo['isSimple'] ? 'simple' : '' ?>">
                            <?=$writeInfo['normalWriteId'].'L'.($key + 1) ?><?= $writeInfo['subWriteId']?>
                        </div>
                    </div>
                </div>

                <? if (!empty($letterInfo['imgPathBack']) && !$writeInfo['isSimple']) { ?>
                    <div class="lettersheet tema">
                        <div class="letterBox tema" style="background-image: url(<?= $letterInfo['imgPathBack'] ?>) !important;"></div>
                    </div>
                <? } ?>
            <? } ?>
        <? } ?>
    </div>
</div>



<script>    
    var list = <?=json_encode($list)?>;  // 서버에서 받은 데이터($list)를 JSON 형식으로 변환하여 JavaScript 변수 'list'에 저장합니다.
        
    // 기본 설정 함수
    function defaultSetup() {        
        window.onafterprint = afterPrintSetup;  // 인쇄가 끝난 후 호출할 함수 지정
                
        printPage();  // 페이지를 인쇄하는 함수 호출        
    }    
    
    // 글씨 크기를 설정하는 함수
    function setLetterFont() {
        let $letterContent = $('.letterContent');  // 클래스 'letterContent'를 가진 요소를 선택합니다.
        let minFontSize = Number.POSITIVE_INFINITY;  // 최소 폰트 크기를 무한대로 설정합니다.

        // 각 textarea 요소에서 글씨 크기를 줄입니다.
        for (let i = 0; i < $letterContent.length; i++) {
            let textarea = $letterContent.eq(i),  // 현재 textarea 요소
                maxHeight = Math.ceil(textarea.height() + 1);  // textarea의 높이를 계산하고 오차 범위를 1로 설정합니다.

            let fontSize = parseFloat(textarea.css('font-size'));  // textarea의 현재 폰트 크기를 가져옵니다.

            // textarea의 내용이 넘칠 때까지 폰트 크기를 줄입니다.
            while (textarea[0].scrollHeight > maxHeight && fontSize > 0) {                   
                console.log(fontSize);  // 폰트 크기를 로그로 출력
                textarea.css('font-size', fontSize + 'px');  // 폰트 크기를 적용
                fontSize -= 0.1;  // 폰트 크기를 0.1씩 줄입니다.
            }
        }        
    }

    // 페이지를 인쇄하는 함수
    function printPage() {
        window.print();  // 브라우저의 인쇄 기능을 실행합니다.
    }    

    // 인쇄 후 호출될 함수
    function afterPrintSetup() {
        window.close();  // 인쇄가 끝난 후 창을 닫습니다.
    }

    // 페이지가 로드된 후 실행될 코드
    window.onload = function() {
        showLoadingBar();  // 로딩 바를 표시하는 함수 호출
        
        // 폰트가 준비되었을 때 실행되는 코드
        document.fonts.ready.then(function() {
            setTimeout(function() {
                setLetterFont();  // 글씨 크기를 설정하는 함수 호출
                hideLoadingBar();  // 로딩 바를 숨깁니다.
                defaultSetup();  // 기본 설정을 실행합니다.
            }, 100);  // 100ms 후에 실행됩니다.
        });        
    };
</script>