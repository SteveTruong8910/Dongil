<link rel="stylesheet" type="text/css" href="/assets/css/view/tmp.css<?=$this->config->item('ver')?>">
<style>
    #ft_menu{ display: none; }
</style>

<div id="tmp" style="margin-top: 20px; padding: 0px 10px;">
    <div class="tmpWrap">
        <? if(!count($list)) { ?>
            <p class="empty">임시 저장된 편지지가 없어요!</p>
        <? }else { ?>
            <strong>총 <?=count($list)?>장</strong>
            <? foreach($list as $key => $info) { ?>
                <div class="viewWrap">
                    <div class="leftBox">                        
                        <img class="viewImg" src="<?=$info['imgPath']?>?v=1"/>
                        <p><?=$info['productName']?></p>                        
                        <p style="font-size: 10px;">편지 <?=$info['totalLetterCnt']?>장/사진 <?=$info['totalPhotoCnt']?>개/문서 <?=$info['totalPdfFileCnt']?>개</p>
                    </div>
                    <div class="rightBox">
                        <p class="saveDate">저장일: <?=$info['tmpSaveRegDate']?></p>
                        <div class="content"><?= nl2br(htmlspecialchars($info['content'][0] ?: '&nbsp;')) ?></div>

                        <div class="btnWrap">
                            <a href="/letter?tmpSaveIdx=<?=$info['idx']?>"class="continueBtn">이어서 작성하기</a>
                            <a class="deleteBtn" onclick="removeLetter(<?=$info['idx']?>)">삭제하기</a>
                        </div>
                    </div>
                </div>
            <? } ?>
        <? } ?>
    </div>
</div>

<script>
    // 편지를 삭제하는 함수
    function removeLetter(letterIdx) {
        // 사용자에게 삭제 확인을 요청하는 팝업 표시
        showConfirm("해당 편지지를 삭제하시겠습니까?")
        .then(async (result) => {
            // 사용자가 '확인'을 누르지 않으면 함수 종료
            if (!result.value) return;

            // 서버로 삭제 요청을 보내는 API 호출
            const onRemoveLetter = await postJson('/userApi/removeLetter', {
                letterIdx: letterIdx  // 삭제할 편지의 인덱스 전달
            });

            // 삭제 요청이 실패한 경우, 오류 메시지를 알림창에 표시하고 종료
            if (!onRemoveLetter.result) {
                swal(onRemoveLetter.msg);
                return false;
            }

            // 삭제 완료 메시지를 사용자에게 알림
            showAlert("삭제되었습니다.")
            .then(() => {
                // 페이지를 새로고침하여 변경 사항 반영
                location.reload();
            });
        });
    }
</script>
