<style>
    .drop-zone {
        border: 2px dashed #aaa;
        padding: 30px;
        text-align: center;
        border-radius: 10px;
        cursor: pointer;
        background-color: #f9f9f9;
        margin-bottom: 10px;
    }

    .drop-zone.dragover {
        background-color: #e0f7fa;
        border-color: #00acc1;
    }

    .drop-zone .drop-input {
        display: none;
    }

    .file-name-preview {
        font-size: 13px;
        color: #555;
        margin-bottom: 10px;
    }

    #pdfThumbnailCanvas {
        display: block !important;
        width: 100% !important;
        height: auto !important;
        border: 1px solid red !important; /* 디버깅용 */
    }

</style>

<!-- 자료실 관리 페이지 -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

<div id="library">
    <p class="title">자료실 관리</p>

    <div class="local">
        총 자료 수 <?=$totalCnt?>개
    </div>

    <div class="topBox">
    <button class="btnRemove btnSubmit" onclick="deleteCategory(<?=$categoryIdx?>)">카테고리 삭제</button>     
        <button class="btnAdd btnSubmit" onclick="openChangeCateModal()">카테고리 이름/순서 변경</button>
        <button class="btnAdd btnSubmit" onclick="openChangeLibraryModal()">자료 순서 변경</button>
        <button class="btnAdd btnSubmit" onclick="setLibraryCategory()">카테고리 등록</button>
        <button class="btnAdd btnSubmit" onclick="openLibraryModal('')">자료 등록</button>
    </div>

    <div class="cateBox">
        <? foreach($cateList as $data){ ?>
            <a href="/admin/library/<?=$data['idx']?>" class="<?=($categoryIdx == $data['idx']) ? 'active' : ''?>">#<?=$data['cateName']?></a>
        <? } ?>
    </div>

    <div class="libraryList">
        <? if(!count($list)){ ?>
            <p class="empty">등록된 자료가 없습니다.</p>
        <? } ?>
        <? foreach($list as $data){ ?>
            <div class="libraryViewBox" onclick='openLibraryModal(<?=json_encode($data)?>)'>
                <div class="libraryImgBox">
                    <img src="<?=$data['thumbPath']?>?v=1"/>
                </div>
                <p><?=$data['title']?></p>
                <p><strong><?=number_format($data['price'])?>원</strong></p>
            </div>
        <? } ?>
    </div>

    <!-- 자료 등록/수정 모달 -->
    <div id="libraryModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <input type="hidden" id="pageCount" value="0"/>

        <div class="modal-dialog" style="width: 600px;">
            <div class="modal-content">
                <div class="modal-body">
                    <i class="fas fa-times" onclick="closeModal()"></i>

                    <input type="hidden" id="libraryIdx" value="0"/>
                    <p>자료 등록</p>

                    <p>자료 이름</p>
                    <input type="text" id="title" placeholder="자료 이름을 입력해주세요.">

                    <p>PDF 파일</p>
                    <div id="dropZone" class="drop-zone">
                        <span class="drop-text">이곳에 PDF 파일을 드래그하거나 클릭해서 업로드</span>
                        <input id="pdfController" type="file" accept="application/pdf" class="drop-input">
                    </div>
                    <div id="fileNamePreview" class="file-name-preview"></div>

                    <canvas id="pdfThumbnailCanvas" style="width: 100%; max-height: 300px; margin-top: 10px; border: 1px solid #ccc;"></canvas>


                    <p>카테고리</p>
                    <select id="categoryIdx">
                        <? foreach($cateList as $data){ ?>
                            <option value="<?=$data['idx']?>"><?=$data['cateName']?></option>
                        <? } ?>
                    </select>

                    <p>가격</p>
                    <input type="number" id="price" placeholder="자료 가격">

                    <button class="btnSubmit" onclick="setLibrary()">저장하기</button>
                    <button id="btnDeleteLibrary" class="btnDelete hide" onclick="deleteLibrary()">삭제하기</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 카테고리 변경 모달 -->
    <div id="changeCateModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" style="width: 600px;">
            <div class="modal-content">
                <div id="sortable" class="modal-body" style="overflow: auto;">
                    <? foreach($cateList as $data){ ?>
                        <div class="sortContent ui-state-default" data-idx="<?=$data['idx'] ?>">
                            <span class="sortCateName" data-idx="<?=$data['idx']?>">#<?=$data['cateName']?></span>
                            <button onclick="changeCateName(<?=$data['idx']?>, '<?=$data['cateName']?>')">이름 변경</button>
                        </div>
                    <? } ?>
                    <button class="btnSubmit" onclick="setChangeCategory()">저장하기</button>
                </div>
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

<!-- 자료 순서 변경 모달 -->
<div id="changeLibraryModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="width: 600px; max-height: 90vh;">
        <div class="modal-content">
            <div id="sortableLibrary" class="modal-body" style="overflow: auto;">
                <!-- 자료 순서를 변경할 수 있는 리스트 -->
                <? foreach($list as $key => $data){ ?>
                    <div class="sortLibraryContent ui-state-default" data-idx="<?=$data['idx']?>">
                        <p><?=$data['title']?></p>
                    </div>                       
                <? } ?>
                <button class="btnSubmit" onclick="setChangeLibrary()">저장하기</button>
            </div>
        </div>
    </div>
</div>

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js';
    // pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js';

    // jQuery UI의 sortable 기능을 적용하여 카테고리 및 편지지 목록 정렬 가능하게 함
    $("#sortable, #sortableLibrary").sortable();

    function openLibraryModal(data) {
        selectedPdfFile = null;
        if (!data) {
            $('#libraryIdx').val(0);
            $('#title').val('');
            $('#price').val('');
            $('#fileNamePreview').text('');
            $('#pdfController').val('');
            $('#pageCount').val('0');
            $('#btnDeleteLibrary').addClass('hide');

            // canvas 초기화
            const canvas = document.getElementById('pdfThumbnailCanvas');
            const context = canvas.getContext('2d');
            console.log('canvas', canvas);
            console.log('context', context);
            context.clearRect(0, 0, canvas.width, canvas.height);
        } else {
            $('#libraryIdx').val(data.idx);
            $('#categoryIdx').val(data.categoryIdx);
            $('#title').val(data.title || '');
            $('#price').val(data.price);
            $('#fileNamePreview').text(data.originalFileName || '');
            $('#pageCount').val(data.pageCount); 
            $('#btnDeleteLibrary').removeClass('hide');

            // 썸네일은 기존 이미지 보여줄 필요 없다면 비움
            const canvas = document.getElementById('pdfThumbnailCanvas');
            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height);
        }

        $('#libraryModal').modal('show');
    }

    async function renderPdfThumbnail(file) {
        const fileReader = new FileReader();
        fileReader.onload = async function() {
            const typedarray = new Uint8Array(this.result);
            try {
                const pdf = await pdfjsLib.getDocument({ data: typedarray }).promise;
                const page = await pdf.getPage(1);
                const scale = 1.5;
                const viewport = page.getViewport({ scale });

                const canvas = document.getElementById('pdfThumbnailCanvas');
                const context = canvas.getContext('2d');
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                await page.render({ canvasContext: context, viewport }).promise;

                $('#pageCount').val(pdf.numPages);
                console.log("썸네일 생성 완료");
            } catch (error) {
                console.error('PDF 분석 오류:', error);
                showAlert('PDF 분석 실패');
            }
        };
        fileReader.readAsArrayBuffer(file);
    }



    function closeModal() {
        $('#libraryModal').modal('hide');
    }

    /**
     * 새로운 카테고리 등록 함수
     */
    async function setLibraryCategory(){
        let cateName = prompt('카테고리 이름을 입력해주세요.');                

        if (!cateName) {
            return false; // 사용자가 입력하지 않으면 종료
        }

        // 서버에 새로운 카테고리 등록 요청
        const setLibraryCategoryRes = await postJson('/adminApi/setLibraryCategory', {
            cateName : cateName
        });

        // 요청이 실패하면 알림 후 종료
        if (!setLibraryCategoryRes.result) {
            showAlert(setLibraryCategoryRes.msg);
            return false;
        }

        // 성공 시 알림을 띄운 후 해당 카테고리 페이지로 이동
        showAlert("등록되었습니다.")
        .then(() => {
            location.href = `/admin/library/${setLibraryCategoryRes.categoryIdx}`;
        });
    }

    async function setLibrary() {
        const btn = document.querySelector('.btnSubmit');
        if (btn.disabled) return; // 이미 처리 중이면 아무 것도 안 함
        btn.disabled = true;
        btn.innerText = '저장 중...';

        const libraryIdx = $('#libraryIdx').val();
        const title = $('#title').val();
        const price = $('#price').val();
        const categoryIdx = $('#categoryIdx').val();
        const pageCount = $('#pageCount').val();
        const pdfFile = $('#pdfController')[0].files[0];

        const canvas = document.getElementById('pdfThumbnailCanvas');
        console.log(canvas.width, canvas.height);
        const thumbnailBase64 = canvas.toDataURL('image/jpeg');

        if (!title) {
            showAlert('자료 이름을 입력해주세요.');
            return;
        }

        if (!pdfFile && libraryIdx == 0) {
            showAlert('PDF 파일을 선택해주세요.');
            return;
        }

        let formData = new FormData();
        formData.append('libraryIdx', libraryIdx);
        formData.append('title', title);
        formData.append('price', price);
        formData.append('categoryIdx', categoryIdx);
        formData.append('pageCount', pageCount);
        formData.append('thumbnailBase64', thumbnailBase64);

        if (pdfFile) {
            formData.append('pdf', pdfFile); // key 이름 중요: 서버에서도 'pdf'로 받아야 함
            const canvas = document.getElementById('pdfThumbnailCanvas');
            const thumbnailBase64 = canvas.toDataURL('image/jpeg');
            formData.append('thumbnailBase64', thumbnailBase64);
        }

        console.log("썸네일 Base64:", thumbnailBase64.substring(0, 100));

        try {
            const res = await fetch('/adminApi/setLibrary', {
                method: 'POST',
                body: formData
            });

            const result = await res.json();

            if (!result.result) {
                showAlert(result.msg || '저장 중 오류가 발생했습니다.');
                return;
            }

            showAlert('저장되었습니다.')
                .then(() => {
                    location.reload();
                });
        } catch (err) {
            console.error('에러 발생:', err);
            showAlert('서버 오류가 발생했습니다.');
        }
    }

    /**
     * 카테고리 순서 변경 모달 열기
     */
    function openChangeCateModal() {
        $('#changeCateModal').modal('show');
    }

    /**
     * 카테고리 변경 모달 닫기
     */
    function closeChangeCateModal() {
        $('#changeCateModal').modal('hide');
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
        const changeCategoryRes = await postJson('/adminApi/changeLibraryCategory', {
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
     * 카테고리를 삭제하는 함수
     * @param {number} categoryIdx - 삭제할 카테고리의 ID
     */
    async function deleteCategory(categoryIdx) {
        // 사용자 확인 메시지
        if (!confirm('해당 카테고리 삭제시\n해당 카테고리에 등록된 자료가 모두 삭제됩니다.')) return false;
        console.log(categoryIdx);

        // 서버에 카테고리 삭제 요청
        const deleteCategoryRes = await postJson('/adminApi/deleteLibraryCategory', {
            categoryIdx: categoryIdx
        });

        // 요청 실패 시 알림 후 종료
        if (!deleteCategoryRes.result) {
            showAlert(deleteCategoryRes.msg);
            return false;
        }

        // 성공 시 알림을 띄운 후 자료 카테고리 페이지로 이동
        showAlert("삭제되었습니다.")
            .then(() => {
                location.href = `/admin/library`;
            });
    }

    async function deleteLibrary() {
        const confirmDelete = confirm('정말 삭제하시겠습니까?');
        if (!confirmDelete) return;

        const libraryIdx = $('#libraryIdx').val();
        if (!libraryIdx || libraryIdx == 0) {
            showAlert('삭제할 자료를 선택해주세요.');
            return;
        }

        try {
            const res = await fetch('/adminApi/deleteLibrary', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ libraryIdx })
            });

            const result = await res.json();

            if (!result.result) {
                showAlert(result.msg || '삭제 중 오류가 발생했습니다.');
                return;
            }

            showAlert('삭제되었습니다.')
                .then(() => {
                    location.reload();
                });
        } catch (err) {
            console.error('삭제 오류:', err);
            showAlert('서버 오류가 발생했습니다.');
        }
    }

    /**
     * 카테고리 이름 변경 함수
     * @param {number} categoryIdx - 변경할 카테고리 ID
     * @param {string} oldCateName - 기존 카테고리 이름
     */
    async function changeCateName(categoryIdx, oldCateName) {
        let cateName = prompt('변경하실 카테고리 이름을 입력해주세요.', oldCateName);

        if (!cateName) {
            return false; // 사용자가 입력하지 않으면 종료
        }

        // 서버에 카테고리 이름 변경 요청
        const setChangeCateNameRes = await postJson('/adminApi/changeLibraryCateName', {
			categoryIdx : categoryIdx,
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
            $(`.sortCateName[data-idx="${categoryIdx}"]`).text('#' + cateName);
        });        
    }
    
    /**
     * 자료 순서 변경 모달 닫기
     */
    function closeChangeLibraryModal() {
        $('#changeLibraryModal').modal('hide');
    }

    /**
     * 자료 순서 변경 모달 열기
     */
    function openChangeLibraryModal() {
        $('#changeLibraryModal').modal('show');
    }

    /**
     * 자료 순서를 변경하고 서버에 저장하는 함수
     */
    async function setChangeLibrary() {
        let $sortContent = $('.sortLibraryContent'), // 정렬된 자료 요소 목록
            idxArr = [];

        // 현재 정렬된 순서대로 자료 인덱스를 배열에 저장
        for (let i = 0; i < $sortContent.length; i++) {
            idxArr.push($sortContent.eq(i).data('idx'));
        }

        // 서버에 변경된 순서를 요청
        const setChangeLibraryRes = await postJson('/adminApi/changeLibrary', {
            idxArr: idxArr
        });

        // 요청 실패 시 알림 후 종료
        if (!setChangeLibraryRes.result) {
            showAlert(setChangeLibraryRes.msg);
            return false;
        }

        // 성공 시 알림을 띄운 후 페이지 새로고침
        showAlert("저장되었습니다.")
            .then(() => {
                location.reload();
            });
    }



    const dropZone = document.getElementById('dropZone');
    const input = document.getElementById('pdfController');
    const preview = document.getElementById('fileNamePreview');

    dropZone.addEventListener('click', () => input.click());

    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            input.files = files;
            preview.textContent = files[0].name;
            selectedPdfFile = files[0];

            renderPdfThumbnail(selectedPdfFile);
        }
    });


    let selectedPdfFile = null;

    input.addEventListener('change', () => {
        if (input.files.length > 0) {
            preview.textContent = input.files[0].name;
            selectedPdfFile = input.files[0];

            // 모달이 열려있으면 바로 렌더링
            if ($('#libraryModal').is(':visible')) {
                renderPdfThumbnail(selectedPdfFile);
            } else {
                // 안 열려있으면 모달 열린 다음에 렌더링
                $('#libraryModal').one('shown.bs.modal', () => {
                    renderPdfThumbnail(selectedPdfFile);
                });
            }
        }
    });



    async function renderPdfThumbnail(file) {
        const fileReader = new FileReader();
        fileReader.onload = async function () {
            const typedarray = new Uint8Array(this.result);
            try {
                const pdf = await pdfjsLib.getDocument({ data: typedarray }).promise;
                const page = await pdf.getPage(1);
                const scale = 1.5;
                const viewport = page.getViewport({ scale });

                const canvas = document.getElementById('pdfThumbnailCanvas');
                const context = canvas.getContext('2d');
                if (!context) {
                    console.error('캔버스 컨텍스트 불러오기 실패!');
                    return;
                }
                canvas.width = viewport.width;
                canvas.height = viewport.height;

                await page.render({ canvasContext: context, viewport }).promise;
                $('#pageCount').val(pdf.numPages);
                console.log("썸네일 생성 완료");
            } catch (error) {
                console.error('PDF 분석 오류:', error);
                showAlert('PDF 분석 실패');
            }
        };
        fileReader.readAsArrayBuffer(file);
    }


//     // input.addEventListener('change', async () => {
//     // if (input.files.length > 0) {
//     //     preview.textContent = input.files[0].name;

//     //     const file = input.files[0];
//     //     const fileReader = new FileReader();
//     //     fileReader.onload = async function() {
//     //     console.log("파일 로드됨");

//     //     const typedarray = new Uint8Array(this.result);
//     //     try {
//     //         const pdf = await pdfjsLib.getDocument({ data: typedarray }).promise;
//     //         console.log("PDF 로딩 성공");

//     //         const page = await pdf.getPage(1);
//     //         const scale = 1.5;
//     //         const viewport = page.getViewport({ scale });

//     //         const canvas = document.getElementById('pdfThumbnailCanvas');
//     //         const context = canvas.getContext('2d');
//     //         canvas.width = viewport.width;
//     //         canvas.height = viewport.height;

//     //         await page.render({ canvasContext: context, viewport }).promise;

//     //         $('#pageCount').val(pdf.numPages);
//     //         console.log("썸네일 생성 완료");
//     //     } catch (error) {
//     //         console.error('PDF 분석 오류:', error);
//     //     }
//     // };

//     //     fileReader.readAsArrayBuffer(file);
//     // }
// });


</script>
