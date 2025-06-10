<link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>

<!-- 편지지 관리 페이지 -->
<div id="letter">
    <!-- 페이지 제목 -->
    <p class="title">편지지 관리</p>
    
    <!-- 총 편지지 수 표시 -->
    <div class="local">
        총 편지지 수 <?=$totalCnt?>개
    </div>
    
    <!-- 상단 버튼들 (카테고리 삭제, 이름/순서 변경, 편지지 순서 변경) -->
    <div class="topBox">
        <? if($cateIdx >= 3){ ?>
            <button class="btnRemove btnSubmit" onclick="deleteCategory(<?=$temaCateIdx?>)">카테고리 삭제</button>                         
            <button class="btnAdd btnSubmit" onclick="openChangeCateModal()">카테고리 이름/순서 변경</button>            
            <button class="btnAdd btnSubmit" onclick="openChangeLetterModal()">편지지 순서변경</button>            
        <? } ?>
        <!-- 편지지 등록 버튼 -->
        <button class="btnAdd btnSubmit" onclick="openLetterModal('')">편지지 등록</button>        
        <!-- 테마 카테고리 등록 버튼 -->
        <button class="btnAdd btnSubmit" onclick="setCategory()">테마 카테고리 등록</button>        
    </div>
    
    <!-- 카테고리 목록 (전체 카테고리 리스트) -->
    <div class="cateBox">
        <? foreach($cateList as $key => $data){ 
            if($key == 3) break;  /* 카테고리가 3개까지만 표시(일반, 심플, 테마) */
        ?>
            <a href="/admin/letter/<?=$data['idx']?>" class="<?=($cateIdx == $data['idx'])? 'active' : ''?>">#<?=$data['cateName']?></a>
        <? } ?>
    </div>
    
    <!-- 테마 카테고리 목록(테마 서브 카테고리) (cateIdx가 3 이상일 때만 표시) -->
    <? if($cateIdx >= 3){ /*테마*/ ?>
        <div class="cateBox">
            <? foreach($cateList as $key => $data){ 
                if($key < 3) continue;  /* 3번 이상인 카테고리만 표시 */
            ?>
                <a href="/admin/letter/3/<?=$data['idx']?>" class="<?=($temaCateIdx == $data['idx'])? 'active' : ''?>">
                    #<?=$data['cateName']?>
                </a>
            <? } ?>
        </div>
    <? } ?>    
    
    <!-- 편지지 목록 -->
    <div class="letterList">
        <!-- 편지지가 없을 때 표시 -->
        <? if(!count($list)){ ?>
            <p class="empty">등록된 편지지가 없습니다.</p>
        <? } ?>
        <!-- 편지지 목록 출력 -->
        <? foreach($list as $key => $data){ ?>
            <div class="letterViewBox" onclick='openLetterModal(<?=json_encode($data)?>)'>
                <div class="letterImgBox">
                    <img src="<?=$data['imgPath']?>?v=1"/>
                </div>
                <p><?=$data['name']?></p>
                <p><strong><?=number_format($data['price'])?>원</strong></p>
            </div>
        <? } ?>
    </div>
    
    <!-- 편지지 등록/수정 모달 -->
    <div id="letterModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" style="width: 1000px;">
            <div class="modal-content">
                <div class="modal-body">                    
                    <i class="fas fa fa-times" onclick="closeModal()"></i> <!-- 모달 닫기 아이콘 -->

                    <!-- 편지지 관련 데이터 -->
                    <input type="hidden" id="letterIdx" value="0"/>

                    <p>편지지등록</p>
                    <div style="margin-bottom:20px;">
                        <div id="dirFront" class="letterDir active" data-dir="front" onclick="changeDir($(this))">앞면</div>
                        <div id="dirBack" class="letterDir" data-dir="back" onclick="changeDir($(this))">뒷면</div>
                        <div id="dirOriginal" class="letterDir hide" data-dir="oiginal" onclick="changeDir($(this))">그림</div>
                    </div>

                    <!-- 편지지 등록/수정 폼 -->
                    <div class="letterFormBox">                        
                        <div id="letterBox" class="letterBox">
                            <textarea id="letterContent">텍스트를 입력하면서 라인간격을 맞춰주세요.</textarea> <!-- 편지지 내용 입력란 -->
                        </div>
                        <!-- 다른 면/그림 선택 시 나오는 입력폼 -->
                        <div id="letterBackBox" class="letterBox hide"></div>
                        <div id="letterOnebonBox" class="letterBox hide"></div>
                        <div id="letterInputBox" class="letterInputBox">
                            <!-- 이미지 등록 버튼 -->
                            <button id="btnSetThumnail" class="btnSubmit" onclick="setThumbnailImg()">이미지 등록</button>
                            <input id="thumbnailController" type="file" class="hide" accept="image/*">

                            <!-- 카테고리 선택 -->
                            <p>카테고리</p>
                            <select id="cateIdx">
                                <? foreach($cateList as $key => $data){ ?>
                                    <option value="<?=$data['idx']?>"><?=$data['cateName']?></option>
                                <? } ?>
                            </select>
                            

                            <p>편지지명</p>
                            <input type="text" id="name" value="" placeholder="편지지명"/>

                            <!-- 기타 입력 필드들 (여백, 가격 등) -->
                            <p>위 여백 px</p>
                            <input type="number" id="topPadding" value="" placeholder="위 여백 px"/>

                            <p>텍스트창 가로사이즈</p>
                            <input type="number" id="contextWidth" value="" placeholder="텍스트창 가로사이즈 px"/>

                            <p>텍스트창 세로사이즈</p>
                            <input type="number" id="contextHeight" value="" placeholder="텍스트창 세로사이즈 px"/>

                            <p>라인 간격(고정)</p>
                            <input type="text" id="contextLineHeight" value="" placeholder="라인간격 px"/>                        
                            
                            <p>편지지 가격</p>
                            <input type="number" id="price" value="" placeholder="1장당 가격"/>
                            
                            <p>최대 라인수</p>
                            <input type="number" id="maxLine" value="" placeholder="최대 라인수"/>
                        </div>
                    </div>                                

                    <!-- 저장 및 삭제 버튼 -->
                    <button class="btnSubmit" onclick="setLetter()">저장하기</button>
                    <button id="btnDeleteLetter" class="btnDelete hide" onclick="deleteLetter()">삭제하기</button>
                </div>
            </div>
        </div>
    </div>  
    
    <!-- 카테고리 이름/순서 변경 모달 -->
    <div id="changeCateModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" style="width: 600px; max-height: 90vh;">
            <div class="modal-content">
                <div id="sortable" class="modal-body" style="overflow: auto;">
                    <!-- 카테고리 순서를 변경할 수 있는 리스트 -->
                    <? foreach($cateList as $key => $data){ 
                        if($key < 3) continue;  /* 테마 서브 카테고리만 표시 */
                    ?>
                        <div class="sortContent ui-state-default" data-idx="<?=$data['idx']?>">
                            <span class="sortCateName" data-idx="<?=$data['idx']?>">#<?=$data['cateName']?></span>
                            <button onclick="changeCateName(<?=$data['idx']?>, '<?=$data['cateName']?>')">이름 변경</button>
                        </div>
                    <? } ?> 
                    <button class="btnSubmit" onclick="setChangeCategory()">저장하기</button>                   
                </div>
            </div>
        </div>
    </div>
    
    <!-- 편지지 순서 변경 모달 -->
    <div id="changeLetterModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" style="width: 600px; max-height: 90vh;">
            <div class="modal-content">
                <div id="sortableLetter" class="modal-body" style="overflow: auto;">
                    <!-- 편지지 순서를 변경할 수 있는 리스트 -->
                    <? foreach($list as $key => $data){ ?>
                        <div class="sortLetterContent ui-state-default" data-idx="<?=$data['idx']?>">
                            <p><?=$data['name']?></p>
                        </div>                       
                    <? } ?>
                    <button class="btnSubmit" onclick="setChangeLetter()">저장하기</button>
                </div>
            </div>
        </div>
    </div>
</div>


<script>    
    
   // 현재 선택된 메인 카테고리 인덱스 설정
    const mainCateIdx = <?=$cateIdx?>;    

    // 썸네일 이미지 배열 (앞면, 뒷면, 원본)
    var thumbnailImgArr = [],
        thumbnailImgBackArr = [],
        thumbnailImgOnebonArr = [];

    // jQuery UI의 sortable 기능을 적용하여 카테고리 및 편지지 목록 정렬 가능하게 함
    $("#sortable, #sortableLetter").sortable();

    /**
     * 편지지 앞면/뒷면/원본 선택 변경 함수
     * @param {jQuery Object} $this - 클릭한 요소
     */
    function changeDir($this){
        let dir = $this.data('dir'); // 선택한 방향(front, back, onebon)

        // 모든 선택 버튼에서 active 클래스 제거 후 현재 선택한 버튼에 추가
        $('.letterDir').removeClass('active');
        $this.addClass('active');       

        // 모든 편지지 영역을 숨김
        $('#letterBox, #letterBackBox, #letterOnebonBox').addClass('hide');

        // 선택한 방향에 따라 해당 영역을 표시
        if(dir == 'front'){
            $('#letterBox').removeClass('hide');            
        }else if(dir == 'back'){ 
            $('#letterBackBox').removeClass('hide');
        }else { 
            $('#letterOnebonBox').removeClass('hide');
        }
    }

    /**
     * 카테고리 이름 변경 함수
     * @param {number} cateIdx - 변경할 카테고리 ID
     * @param {string} oldCateName - 기존 카테고리 이름
     */
    async function changeCateName(cateIdx, oldCateName) {
        let cateName = prompt('변경하실 카테고리 이름을 입력해주세요.', oldCateName);

        if (!cateName) {
            return false; // 사용자가 입력하지 않으면 종료
        }

        // 서버에 카테고리 이름 변경 요청
        const setChangeCateNameRes = await postJson('/adminApi/changeCateName', {
            cateIdx : cateIdx,
            cateName : cateName
        });

        // 요청이 실패하면 알림 후 종료
        if (!setChangeCateNameRes.result) {
            showAlert(setChangeCateNameRes.msg);
            return false;
        }

        // 성공 시 알림을 띄운 후 화면 업데이트
        showAlert("수정되었습니다.")
        .then(() => {
            $(`.sortCateName[data-idx="${cateIdx}"]`).text('#' + cateName);
        });        
    }

    /**
     * 새로운 카테고리 등록 함수
     */
    async function setCategory(){
        let cateName = prompt('카테고리 이름을 입력해주세요.');                

        if (!cateName) {
            return false; // 사용자가 입력하지 않으면 종료
        }

        // 서버에 새로운 카테고리 등록 요청
        const setCategoryRes = await postJson('/adminApi/setCategory', {
            cateName : cateName
        });

        // 요청이 실패하면 알림 후 종료
        if (!setCategoryRes.result) {
            showAlert(setCategoryRes.msg);
            return false;
        }

        // 성공 시 알림을 띄운 후 해당 카테고리 페이지로 이동
        showAlert("등록되었습니다.")
        .then(() => {
            location.href = `/admin/letter/3/${setCategoryRes.cateIdx}`;
        });
    }

    /**
     * 카테고리 순서 변경 모달 열기
     */
    function openChangeCateModal() {
        $('#changeCateModal').modal('show');
    }

    
    /**
     * 카테고리 순서 변경을 서버에 저장하는 함수
     */
    async function setChangeCategory() {
        let $sortContent = $('.sortContent'), // 정렬된 카테고리 요소 목록
            idxArr = [];

        // 현재 정렬된 순서대로 카테고리 인덱스를 배열에 저장
        for (let i = 0; i < $sortContent.length; i++) {
            idxArr.push($sortContent.eq(i).data('idx'));
        }

        // 서버에 변경된 순서를 요청
        const changeCategoryRes = await postJson('/adminApi/changeCategory', {
            idxArr: idxArr
        });

        // 요청 실패 시 알림 후 종료
        if (!changeCategoryRes.result) {
            showAlert(changeCategoryRes.msg);
            return false;
        }

        // 성공 시 알림을 띄운 후 페이지 새로고침
        showAlert("저장되었습니다.")
            .then(() => {
                location.reload();
            });
    }

    /**
     * 편지 순서 변경 모달 닫기
     */
    function closeChangeLetterModal() {
        $('#changeLetterModal').modal('hide');
    }

    /**
     * 편지 순서 변경 모달 열기
     */
    function openChangeLetterModal() {
        $('#changeLetterModal').modal('show');
    }

    /**
     * 편지 순서를 변경하고 서버에 저장하는 함수
     */
    async function setChangeLetter() {
        let $sortContent = $('.sortLetterContent'), // 정렬된 편지 요소 목록
            idxArr = [];

        // 현재 정렬된 순서대로 편지 인덱스를 배열에 저장
        for (let i = 0; i < $sortContent.length; i++) {
            idxArr.push($sortContent.eq(i).data('idx'));
        }

        // 서버에 변경된 순서를 요청
        const setChangeLetterRes = await postJson('/adminApi/changeLetter', {
            idxArr: idxArr
        });

        // 요청 실패 시 알림 후 종료
        if (!setChangeLetterRes.result) {
            showAlert(setChangeLetterRes.msg);
            return false;
        }

        // 성공 시 알림을 띄운 후 페이지 새로고침
        showAlert("저장되었습니다.")
            .then(() => {
                location.reload();
            });
    }

    /**
     * 카테고리 변경 모달 닫기
     */
    function closeChangeCateModal() {
        $('#changeCateModal').modal('hide');
    }

    /**
     * 카테고리를 삭제하는 함수
     * @param {number} cateIdx - 삭제할 카테고리의 ID
     */
    async function deleteCategory(cateIdx) {
        // 사용자 확인 메시지
        if (!confirm('해당 카테고리 삭제시\n해당 카테고리에 등록된 편지지가 모두 삭제됩니다.')) return false;

        // 서버에 카테고리 삭제 요청
        const deleteCategoryRes = await postJson('/adminApi/deleteCategory', {
            cateIdx: cateIdx
        });

        // 요청 실패 시 알림 후 종료
        if (!deleteCategoryRes.result) {
            showAlert(deleteCategoryRes.msg);
            return false;
        }

        // 성공 시 알림을 띄운 후 편지 카테고리 페이지로 이동
        showAlert("삭제되었습니다.")
            .then(() => {
                location.href = `/admin/letter`;
            });
    }

    /**
     * 편지지를 삭제하는 함수
     */
    async function deleteLetter() {
        // 사용자 확인 메시지
        if (!confirm('해당 편지지를 삭제하시겠습니까?')) return false;

        // 서버에 편지 삭제 요청
        const deleteLetterRes = await postJson('/adminApi/deleteLetter', {
            letterIdx: $('#letterIdx').val() // 현재 선택된 편지 ID
        });

        // 요청 실패 시 알림 후 종료
        if (!deleteLetterRes.result) {
            showAlert(deleteLetterRes.msg);
            return false;
        }

        // 성공 시 알림을 띄운 후 페이지 새로고침
        showAlert("삭제되었습니다.")
            .then(() => {
                location.reload();
            });
    }

    
    // 편지 모달을 여는 함수
    function openLetterModal(data) {
        // jQuery를 사용하여 필요한 요소들을 변수에 저장
        let $letterIdx = $('#letterIdx'),
            $letterBox = $('#letterBox'),
            $letterBackBox = $('#letterBackBox'),
            $letterOnebonBox = $('#letterOnebonBox'),
            $letterContent = $('#letterContent'),
            $cateIdx = $('#cateIdx'),
            $name = $('#name'),
            $topPadding = $('#topPadding'),
            $contextWidth = $('#contextWidth'),
            $contextHeight = $('#contextHeight'),
            $contextLineHeight = $('#contextLineHeight'),
            $price = $('#price'),
            $maxLine = $('#maxLine'),
            $btnDeleteLetter = $('#btnDeleteLetter');

        // 편지 방향 설정 (앞면을 기본으로 설정)
        changeDir($('#dirFront'));

        // 데이터가 없을 경우 (새 편지 작성 모드)
        if (!data) {
            // 썸네일 이미지 배열 초기화
            thumbnailImgArr = [];
            thumbnailImgBackArr = [];
            thumbnailImgOnebonArr = [];

            // 기본 이미지로 설정
            $letterIdx.val(0);
            $letterBox.css('background-image', 'url(/assets/image/no_image.jpg?v=1)');
            $letterBackBox.css('background-image', 'url(/assets/image/no_image.jpg?v=1)');
            $letterOnebonBox.css('background-image', 'url(/assets/image/no_image.jpg?v=1)');

            // 기본 카테고리 설정
            $cateIdx.val(mainCateIdx).trigger('change');

            // 입력 필드 초기화
            $name.val('');
            $letterContent.val('').addClass('hide'); // 숨김 처리

            // 기본 레이아웃 크기 설정
            $topPadding.val(letterSize.topPadding);
            $contextWidth.val(letterSize.contextWidth);
            $contextHeight.val(letterSize.contextHeight);
            $contextLineHeight.val(letterSize.contextLineHeight);
            $maxLine.val(letterSize.maxLine);

            // 스타일 적용
            $letterBox.css('padding-top', letterSize.topPadding + 'px');
            $letterContent.css('width', letterSize.contextWidth + 'px');
            $letterContent.css('height', letterSize.contextHeight + 'px');
            $letterContent.css('line-height', letterSize.contextLineHeight + 'px');

            // 가격 초기화
            $price.val(0);

            // 삭제 버튼 숨김
            $btnDeleteLetter.addClass('hide');
        } else {
            // 기존 데이터를 불러와서 모달을 채움 (편지 수정 모드)
            thumbnailImgArr = data.thumbnail;
            thumbnailImgBackArr = data.thumbnailBack;
            thumbnailImgOnebonArr = data.thumbnailOriginal;

            // 기존 편지 데이터 적용
            $letterIdx.val(data.idx);
            $letterBox.css('background-image', `url(${data.imgPath}?v=1)`);
            $letterBackBox.css('background-image', `url(${data.imgBackPath}?v=1)`);
            $letterOnebonBox.css('background-image', `url(${data.imgOnebonPath}?v=1)`);
            $cateIdx.val(data.cateIdx).trigger('change');
            $name.val(data.name);

            // 편지 내용을 보이도록 설정
            $letterContent.val('').removeClass('hide');

            // 기존 설정값 적용
            $topPadding.val(data.topPadding);
            $contextWidth.val(data.contextWidth);
            $contextHeight.val(data.contextHeight);
            $contextLineHeight.val(data.contextLineHeight);

            // 스타일 적용
            $letterBox.css('padding-top', data.topPadding + 'px');
            $letterContent.css('width', data.contextWidth + 'px');
            $letterContent.css('height', data.contextHeight + 'px');
            $letterContent.css('line-height', data.contextLineHeight + 'px');

            // 가격 및 최대 줄 수 적용
            $price.val(data.price);
            $maxLine.val(data.maxLine);

            // 삭제 버튼 보이기
            $btnDeleteLetter.removeClass('hide');
        }

        // 모달 열기
        $('#letterModal').modal('show');
    }

    // 모달을 닫는 함수
    function closeModal() {
        $('#letterModal').modal('hide');
    }

    // 썸네일 이미지 선택창을 여는 함수
    function setThumbnailImg() {
        $('#thumbnailController').click();
    }

    // 썸네일 이미지 파일을 읽고 업로드하는 함수
    async function readThumbnailImg(input) {
        // 파일이 없으면 함수 종료
        if (!input.files.length) return;

        // FormData 객체 생성
        let formData = new FormData();

        // 업로드된 파일을 반복 처리
        for (let i = 0; i < input.files.length; i++) {
            let file = input.files[i],
                extension = file.name.split('.').pop().toLowerCase(),
                imgFileTypes = ['jpg', 'jpeg', 'png', 'gif'], // 허용되는 이미지 확장자
                isImage = imgFileTypes.indexOf(extension) > -1;

            // 이미지 파일이 아닌 경우 경고 메시지 출력 후 종료
            if (!isImage) {
                showAlert('이미지 파일만 업로드 가능합니다.');
                return;
            }

            // FormData에 파일 추가
            formData.append('files[]', file);
        }

        // 기본 너비 추가 (추후 조정 가능)
        formData.append('width', 0);

        // 서버에 파일 업로드 요청
        const setThumbnailImgRes = await postFormJson('/adminApi/upload', formData);

        // 업로드 실패 시 메시지 출력 후 종료
        if (!setThumbnailImgRes.result) {
            showAlert(setThumbnailImgRes.msg);
            return false;
        }

        // 현재 선택된 편지 방향 가져오기
        let dir = $('.letterDir.active').data('dir');

        // 방향별로 썸네일 이미지 설정
        if (dir == 'front') {
            // 앞면 이미지 업데이트
            thumbnailImgArr = setThumbnailImgRes.files;
            $('#letterBox').css('background-image', `url(/assets/upload/${thumbnailImgArr[0].fileName}?v=1)`);
            $('#letterContent').removeClass('hide'); // 내용 보이기
        } else if (dir == 'back') {
            // 뒷면 이미지 업데이트
            thumbnailImgBackArr = setThumbnailImgRes.files;
            $('#letterBackBox').css('background-image', `url(/assets/upload/${thumbnailImgBackArr[0].fileName}?v=1)`);
        } else {
            // 원본 이미지 업데이트
            thumbnailImgOnebonArr = setThumbnailImgRes.files;
            $('#letterOnebonBox').css('background-image', `url(/assets/upload/${thumbnailImgOnebonArr[0].fileName}?v=1)`);
        }

        // 파일 입력 필드 초기화 (같은 파일 다시 선택할 수 있도록)
        input.value = '';
    }
    
    // 편지를 저장하는 비동기 함수
    async function setLetter() {
        // 편지 입력 필드 가져오기
        let $name = $('#name'),
            $topPadding = $('#topPadding'),
            $contextWidth = $('#contextWidth'),
            $contextHeight = $('#contextHeight'),
            $contextLineHeight = $('#contextLineHeight'),
            $price = $('#price'),
            $maxLine = $('#maxLine');

        // 편지지 이름이 비어 있는지 확인
        if (!$name.val()) {
            showAlert('편지지명을 입력해주세요.', $name.focus()); // 알림 후 입력창에 포커스
            return;
        }

        // 서버에 전송할 데이터 객체 생성
        const setLetterRes = await postJson('/adminApi/setLetter', {
            thumbnailImgArr: thumbnailImgArr, // 앞면 썸네일 이미지 배열
            thumbnailImgBackArr: thumbnailImgBackArr, // 뒷면 썸네일 이미지 배열
            thumbnailImgOnebonArr: thumbnailImgOnebonArr, // 원본 썸네일 이미지 배열
            letterIdx: $('#letterIdx').val(), // 편지 ID
            cateIdx: $("#cateIdx option:selected").val(), // 선택된 카테고리 ID
            name: $name.val(), // 편지 이름
            topPadding: $topPadding.val(), // 상단 여백
            contextWidth: $contextWidth.val(), // 편지지 너비
            contextHeight: $contextHeight.val(), // 편지지 높이
            contextLineHeight: $contextLineHeight.val(), // 줄 간격
            price: $price.val(), // 가격
            maxLine: $maxLine.val() // 최대 줄 수
        });

        // 서버 응답이 실패했을 경우 경고창 표시 후 종료
        if (!setLetterRes.result) {
            showAlert(setLetterRes.msg);
            return;
        }

        // 성공적으로 저장되었음을 알리고, 페이지 새로고침
        showAlert("저장되었습니다.")
            .then(() => {
                location.reload(); // 새로고침하여 변경 사항 반영
            });
    }

    // 페이지 로드 후 실행되는 이벤트 핸들러 등록
    $(function () {
        // 썸네일 이미지 변경 이벤트 리스너
        $('#thumbnailController').on('change', function () {
            readThumbnailImg(this); // 이미지 읽기 및 업로드
        });

        // 상단 여백 변경 시 미리보기 반영
        $('#topPadding').on('change', function () {
            $('#letterBox').css('padding-top', $(this).val() + 'px');
        });

        // 편지지 너비 변경 시 미리보기 반영
        $('#contextWidth').on('change', function () {
            $('#letterContent').css('width', $(this).val() + 'px');
        });

        // 편지지 높이 변경 시 미리보기 반영
        $('#contextHeight').on('change', function () {
            $('#letterContent').css('height', $(this).val() + 'px');
        });

        // 줄 간격 변경 시 미리보기 반영
        $('#contextLineHeight').on('change', function () {
            $('#letterContent').css('line-height', $(this).val() + 'px');
        });
    });

    
</script>