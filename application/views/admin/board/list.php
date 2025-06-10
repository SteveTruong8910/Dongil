<div id="notice">
    <p class="title">게시판 관리</p>
    
    <div class="local">
        총 게시글 수 <?=count($list)?>개
    </div>
    
    <? if($type != 'qna'){ ?>
        <div class="topBox">                
            <button class="btnAdd btnSubmit" onclick="goBoardView('<?=$type?>', 0)"><?=$title?> 등록</button>                    
        </div>    
    <? } ?>
    
    <? if($type == 'qna') { ?>
        <form id="searchForm" method="get" style="margin-top: 20px; margin-bottom: 10px;">
            <input type="hidden" name="type" value="<?=$type?>"/>
            
            <? foreach($this->config->item('boardDateState') as $key => $name){ ?>
                <input type="radio" id="dateType<?=$key?>" name="dateType" value="<?=$key?>" <?=$dateType == $key? 'checked' : ''?> onclick="searchForm()">
                <label for="dateType<?=$key?>"><?=$name?></label>
            <? } ?>
        </form>
    <? } ?>
    
    <div id="letter">
        <div class="cateBox">
            <? foreach(array_reverse($this->config->item('noticeCate')) as $key => $name){ 
                if($key == 'review') continue;
            ?>
                <a href="/admin/board?type=<?=$key?>" class="<?=$key == $type? 'active': ''?>"><?=$name?></a>
            <? } ?>
        </div>
    </div>
    
    <table style="margin-top:10px;">
        <thead>
            <tr>                
                <th>제목</th>
                <th width="150">작성일</th>
                <? if($type == 'qna'){ ?>
                    <th width="100">답변여부</th>
                <? } ?>                
            </tr>
        </thead>
        <tbody>
            <? if(!count($list)){ ?>
                <tr>
                    <td colspan="<?=$type == 'qna'? 3 : 2?>" class="empty">게시글이 존재하지않습니다.</td>
                </tr>
            <? } ?>
            
            <? foreach($list as $key => $data){ ?>
                <tr style="cursor:pointer;" onclick="goBoardView('<?=$type?>', <?=$data['idx']?>)">
                    <td><?=$data['title']?></td>
                    <td><?=$data['regDate']?></td>
                    <? if($type == 'qna'){ ?>
                        <td><?=empty($data['answerIdx'])? '미답변' : '답변'?></td>
                    <? } ?>
                </tr>
            <? } ?>
        </tbody>
    </table>
</div>

<script>
    function searchForm(){
        $('#searchForm').submit();
    }
    
    function goBoardView(type, idx){        
        location.href = `/admin/${type}View/${idx}`;
    }
    
</script>