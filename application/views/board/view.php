<style>
    #ft_menu {
        display: none;
    }
    .content img {
        max-width: 100%;   
    }        
</style>
<div id="boardV">
    <p class="date"><?=$info['regDate']?></p>
    <p class="title"><?=$info['title']?></p>
    
    <? if($type == 'review'){ ?>
        <div class="reviewBox" style="margin-bottom: 10px; text-align: center;">
            <? for($star=1; $star<=$info['star']; $star++){ ?>
                <i class="fas fa-star"></i>    
            <? } ?>
            
            <? for($star=$info['star']; $star<5; $star++){ ?>
                <i class="far fa-star"></i>
            <? } ?>
        </div>
        <div class="content">
            <?=nl2br($info['content'])?>
        </div>
    <? }else { ?>
        <div class="content">
            <?=$info['content']?>
        </div>
    <? } ?> 
    
    <? if($type == 'qna'){ ?>
        <div class="answer">
            <div class="answerHead">
                <div class="profileWrap">
                    <i class="fas fa-user"></i>
                    <span>관리자</span>
                </div>
                <div class="date"><?=$isAnswer? $answerInfo['regDate'] : ''?></div>
            </div>

            <? if(!$isAnswer){ ?>
                <div class="empty">
                    곧 답변이 작성될 예정입니다.
                </div>
            <? }else{ ?>
                <div class="content">
                    <?=nl2br($answerInfo['content'])?>
                </div>
            <? } ?>        
        </div>
    <? } ?>    
</div>