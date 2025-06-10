const nav = {    
    /**
     * 메시지를 React Native WebView로 전달하는 함수입니다.
     * 
     * 이 함수는 주어진 메시지를 JSON 형식으로 직렬화하여 
     * `window.ReactNativeWebView.postMessage`를 통해 전달합니다.
     * 
     * @param {Object} message 전송할 메시지 객체
     */
    postMessage: function(message) {        
        window.ReactNativeWebView.postMessage(JSON.stringify(message)); 
    },

    /**
     * 주어진 URL로 페이지를 이동하며, React Native WebView를 통해 네이티브와 통신하는 함수입니다.
     * 
     * 이 함수는 `navigationId`, `isBackRefresh`, `popCnt` 값을 포함한 데이터와 함께
     * React Native WebView로 메시지를 보내 페이지 이동을 처리합니다. 만약 WebView가 없으면 
     * `location.replace`를 사용하여 페이지를 이동시킵니다.
     * 
     * @param {string} url 이동할 URL
     * @param {string} [navigationId=''] 네이티브와 통신 시 사용할 네비게이션 ID
     * @param {boolean} [isBackRefresh=false] 이전 페이지로 돌아갈 때 새로 고침 여부
     * @param {number} [popCnt=2] 뒤로 가기 시에 몇 개의 페이지를 팝할지 설정
     */
    locationReplace: function(url, navigationId = '', isBackRefresh = false, popCnt = 2) {
        if (navigationId && window.ReactNativeWebView) {
            this.postMessage({
                type: 'ROUTER_EVENT',
                data: {
                    navigationId: navigationId,
                    url: url,
                    isBackRefresh: isBackRefresh, 
                    popCnt: popCnt                    
                }
            });
        } else {
            location.replace(url);
        }
    },

    /**
     * 주어진 URL로 페이지를 이동하며, React Native WebView를 통해 네이티브와 통신하는 함수입니다.
     * 
     * 이 함수는 `navigationId`, `isBackRefresh`, `popCnt` 값을 포함한 데이터와 함께
     * React Native WebView로 메시지를 보내 페이지 이동을 처리합니다. 만약 WebView가 없으면 
     * `location.href`를 사용하여 페이지를 이동시킵니다.
     * 
     * @param {string} url 이동할 URL
     * @param {string} [navigationId=''] 네이티브와 통신 시 사용할 네비게이션 ID
     * @param {boolean} [isBackRefresh=false] 이전 페이지로 돌아갈 때 새로 고침 여부
     * @param {number} [popCnt=2] 뒤로 가기 시에 몇 개의 페이지를 팝할지 설정
     */
    locationHref: function(url, navigationId = '', isBackRefresh = false, popCnt = 2) {
        if (navigationId && window.ReactNativeWebView) {            
            this.postMessage({
                type: 'ROUTER_EVENT',
                data: {
                    navigationId: navigationId,
                    url: url,
                    isBackRefresh: isBackRefresh,
                    popCnt: popCnt                    
                }
            });
        } else {
            location.href = url;
        }
    },

    /**
     * 이전 페이지로 돌아가는 함수입니다.
     * 
     * 이 함수는 `window.ReactNativeWebView`가 있을 경우, 네이티브와 통신하여
     * 뒤로 가기 이벤트를 전달하고, 없을 경우 `window.history.back()`을 호출하여
     * 이전 페이지로 돌아갑니다.
     */
    goBack: function() {
        const historyLength = window.history.length;
        const pathName = window.location.pathname;                        

        if (window.ReactNativeWebView) {
            this.postMessage({
                type: 'ROUTER_EVENT',
                data: {
                    navigationId: 'back',
                    url: '',
                    isBackRefresh: false
                }
            });
        } else {
            window.history.back();
        }
    }  
};
