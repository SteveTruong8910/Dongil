<?
    $current_controller = $this->router->fetch_class();
    $current_method = $this->router->fetch_method();
?>
            <div id="ft_menu">                
                <a href="/">
                    <i class="fas fa-home <?=($current_controller == 'home' && $current_method == 'index')? 'active' : ''?>"></i>
                </a>
                                 
                <!-- 편지쓰기 메뉴 일시 중단 -->
                <!--
                <a href="/letter">
                    <i class="fas fa-envelope"></i>
                </a>
                -->

                                
                <a href="/board">
                    <i class="fas fa-bullhorn <?=($current_controller == 'board')? 'active' : ''?>"></i>
                </a>                
                
                                
                <a href="/mypage">
                    <i class="fas fa-user <?=($current_controller == 'mypage')? 'active' : ''?>"></i>
                </a>                
            </div>
        </div> <!--mainWrap-->
	</body>		
</html>