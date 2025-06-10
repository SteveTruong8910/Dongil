<div id="notice">
    <p class="title">팝업 관리</p>
    
    <div class="local">
        총 팝업 수 <?=count($list)?>개
    </div>
        
    <div class="topBox">
        <button class="btnAdd btnSubmit" onclick="setThumbnailImg()">팝업 등록(※1600*2200 비율)</button>
    </div>        
    
    <input id="thumbnailController" type="file" class="hide" accept="image/*">
    
    <table style="margin-top:10px;">
        <thead>
            <tr>                
                <th>이미지</th>                
                <th width="200">작성일</th>
                <th width="150">삭제</th>
            </tr>
        </thead>
        <tbody>
            <? if(!count($list)){ ?>
                <tr>
                    <td colspan="3" class="empty">팝업이 존재하지않습니다.</td>
                </tr>
            <? } ?>
            
            <? foreach($list as $key => $data){ ?>
                <tr>                    
                    <td>
                        <img width="250" src="<?=$data['resizeImgPath']?>"/>
                    </td>                    
                    <td>
                        <?=$data['regDate']?>
                    </td>
                    <td>
                        <button class="btnDownload" onclick="removePopup(<?=$data['idx']?>)">삭제</button>
                    </td>
                </tr>
            <? } ?>
        </tbody>
    </table>
</div>

<script>
    /**
     * 이미지 업로드 및 팝업 등록
     * @param {HTMLElement} input - 파일 입력 요소 (input[type="file"])
     */
    async function readImg(input) {
        // 파일이 선택되지 않았으면 함수 종료
        if (!input.files.length) return;
        
        let formData = new FormData();
        
        // 이미지 파일 검증 및 FormData에 추가
        for (let i = 0; i < input.files.length; i++) {
            let file = input.files[i],
                extension = file.name.split('.').pop().toLowerCase(), // 파일 확장자 추출
                imgFileTypes = ['jpg', 'jpeg', 'png', 'gif'], // 허용 확장자 목록
                isImage = imgFileTypes.indexOf(extension) > -1;

            if (!isImage) {
                showAlert('이미지 파일만 업로드 가능합니다.');
                return;
            }

            // 동일한 파일을 두 번 추가하는 오류 수정 (중복 제거)
            formData.append('files[]', file);
        }
        
        // input 값 초기화 (같은 파일을 다시 선택할 경우 이벤트 발생 안 하는 문제 해결)
        input.value = '';
        
        // 서버에 이미지 업로드 요청
        const imgRes = await postFormJson('/adminApi/popupUpload', formData);

        // 업로드 실패 시 경고 메시지 출력 후 종료
        if (!imgRes.result) {
            showAlert(imgRes.msg);
            return false;
        }
        
        // 업로드된 이미지 정보를 이용하여 팝업 등록 요청
        const savePopupRes = await postJson('/adminApi/savePopup', {
            thumbnailImgArr : imgRes.files
        });
        
        // 팝업 등록 실패 시 경고 메시지 출력 후 종료
        if (!savePopupRes.result){
            showAlert(savePopupRes.msg);
            return;
        }
        
        // 성공 메시지 표시 후 페이지 새로고침
        showAlert('등록되었습니다.')
        .then(() => {
            location.reload(); 
        });
    }

    /**
     * 파일 업로드 버튼 클릭 이벤트 핸들러
     * - 사용자가 버튼을 누르면 파일 선택 창이 열림
     */
    function setThumbnailImg() {
        $('#thumbnailController').click();
    }

    /**
     * 팝업 삭제
     * @param {number} popupIdx - 삭제할 팝업의 고유 ID
     */
    async function removePopup(popupIdx){
        // 사용자에게 삭제 여부 확인
        if (!confirm('해당 팝업을 삭제하시겠습니까?')) return;
        
        // 팝업 사용 여부를 'N'으로 설정하여 비활성화
        const removePopupRes = await postJson('/adminApi/setPopup', {
            popupIdx: popupIdx,
            type: 'isUse',
            value: 'N'
        });

        // 삭제 실패 시 경고 메시지 출력 후 종료
        if (!removePopupRes.result) {
            showAlert(removePopupRes.msg);
            return false;
        }
        
        // 성공 메시지 표시 후 페이지 새로고침
        showAlert('삭제되었습니다.')
        .then(() => {
            location.reload();
        });
    }

    /**
     * 이벤트 바인딩 (DOM이 로드된 후 실행)
     */
    $(function(){
        // 파일 선택(input[type="file"]) 시 readImg 함수 실행
        $('#thumbnailController').on('change', function() {
            readImg(this);
        });
    });
</script>