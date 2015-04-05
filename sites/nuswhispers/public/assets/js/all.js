filepicker.setKey("AnsmRtYIsR9qh79Hxxrpez"),angular.module("nuswhispersApp.services",["facebook"]).config(function(FacebookProvider){"use strict";FacebookProvider.init("563666707081891")}),angular.module("nuswhispersApp.controllers",["nuswhispersApp.services","vcRecaptcha"]);var app=angular.module("nuswhispersApp",["nuswhispersApp.controllers","angular-loading-bar","monospaced.elastic","angularMoment","infinite-scroll","ngCookies","ngResource","ngSanitize","ngRoute","ngAnimate","ui.utils","ui.bootstrap","ui.router","ngGrid"]);app.config(["$routeProvider","$locationProvider","$httpProvider",function($routeProvider,$locationProvider,$httpProvider){"use strict";$routeProvider.when("/home/",{templateUrl:"assets/templates/confessions.html",controller:"ConfessionsController",resolve:{controllerOptions:function(){return{view:"featured"}}}}).when("/popular/",{templateUrl:"assets/templates/confessions.html",controller:"ConfessionsController",resolve:{controllerOptions:function(){return{view:"popular"}}}}).when("/new/",{templateUrl:"assets/templates/confessions.html",controller:"ConfessionsController",resolve:{controllerOptions:function(){return{view:"recent"}}}}).when("/category/:category",{templateUrl:"assets/templates/confessions.html",controller:"ConfessionsController",resolve:{controllerOptions:function(){return{view:"category"}}}}).when("/tag/:tag",{templateUrl:"assets/templates/confessions.html",controller:"ConfessionsController",resolve:{controllerOptions:function(){return{view:"tag"}}}}).when("/confession/:confession",{templateUrl:"assets/templates/confessions.html",controller:"ConfessionsController",resolve:{controllerOptions:function(){return{view:"single"}}}}).when("/search/:query",{templateUrl:"assets/templates/confessions.html",controller:"ConfessionsController",resolve:{controllerOptions:function(){return{view:"search"}}}}).when("/favourites/",{templateUrl:"assets/templates/confessions.html",controller:"ConfessionsController",resolve:{controllerOptions:function(){return{view:"favourites"}}}}).when("/submit/",{templateUrl:"assets/templates/submit.html",controller:"SubmitController"}).otherwise({redirectTo:"/home/"}),$locationProvider.hashPrefix("!"),$httpProvider.defaults.headers.common["X-Requested-With"]="XMLHttpRequest"}]),app.run(["$rootScope",function($rootScope){"use strict";console.log("Angular.js run() function...")}]),angular.module("nuswhispersApp.controllers").controller("ConfessionsController",function($scope,$routeParams,Confession,Facebook,FacebookUser,controllerOptions){"use strict";$scope.getConfessions=function(){function processConfessionResponse(confessions){if(0===confessions.length)$scope.doLoadMoreConfessions=!1;else{var confessionModels=[];for(var i in confessions)confessionModels.push(new Confession(confessions[i]));$scope.confessions.push.apply($scope.confessions,confessionModels),$scope.offset+=$scope.count}$scope.loadingConfessions=!1}switch($scope.loadingConfessions=!0,$scope.view=controllerOptions.view,controllerOptions.view){case"single":Confession.getConfessionById($routeParams.confession).success(function(response){response.success&&processConfessionResponse([response.data.confession]),$scope.doLoadMoreConfessions=!1,$scope.loadingConfessions=!1});break;case"recent":Confession.getRecent($scope.timestamp,$scope.offset,$scope.count).success(function(response){processConfessionResponse(response.data.confessions)});break;case"popular":Confession.getPopular($scope.timestamp,$scope.offset,$scope.count).success(function(response){processConfessionResponse(response.data.confessions)});break;case"category":Confession.getCategory($routeParams.category,$scope.timestamp,$scope.offset,$scope.count).success(function(response){processConfessionResponse(response.data.confessions)});break;case"tag":Confession.getTag($routeParams.tag,$scope.timestamp,$scope.offset,$scope.count).success(function(response){processConfessionResponse(response.data.confessions)});break;case"search":Confession.search($routeParams.query,$scope.timestamp,$scope.offset,$scope.count).success(function(response){processConfessionResponse(response.data.confessions)});break;case"favourites":Confession.getFavourites($scope.timestamp,$scope.offset,$scope.count).success(function(response){response.success?processConfessionResponse(response.data.confessions):($scope.doLoadMoreConfessions=!1,$scope.loadingConfessions=!1)});break;default:Confession.getFeatured($scope.timestamp,$scope.offset,$scope.count).success(function(response){processConfessionResponse(response.data.confessions)})}},$scope.timestamp=Math.floor(Date.now()/1e3),$scope.offset=0,$scope.count=5,$scope.loadingConfessions=!1,$scope.doLoadMoreConfessions=!0,$scope.confessions=[],$scope.fbUser=FacebookUser,$scope.getConfessions(),$scope.processConfessionContent=function(content){var splitContentTags=content.split(/(#\w+)/),processedContent="";for(var i in splitContentTags)processedContent+=/(#\w+)/.test(splitContentTags[i])?'<a href="/#!tag/'+splitContentTags[i].substring(1)+'">'+splitContentTags[i]+"</a>":splitContentTags[i];return processedContent},$scope.confessionIsFavourited=function(confession){if(""!==FacebookUser.getAccessToken()){var fbUserID=parseInt(FacebookUser.getUserID());for(var i in confession.favourites)if(confession.favourites[i].fb_user_id===fbUserID)return!0}return!1},$scope.toggleFavouriteConfession=function(confession){""!==FacebookUser.getAccessToken()&&($scope.confessionIsFavourited(confession)?confession.unfavourite().success(function(response){response.success&&confession.load()}):confession.favourite().success(function(response){response.success&&confession.load()}))},$scope.confessionIsLiked=function(confession){if(""!==FacebookUser.getAccessToken()){var fbUserID=FacebookUser.getUserID(),fbConfessionLikes=confession.facebook_information.likes.data;for(var i in fbConfessionLikes)if(fbConfessionLikes[i].id===fbUserID)return!0}return!1},$scope.shareConfessionFB=function(confessionID){Facebook.ui({method:"share",href:"http://nuswhispers.com/#!/confession/"+confessionID})}}),angular.module("nuswhispersApp.controllers").controller("MainController",function($scope,$location,$route,Facebook,FacebookUser,Tag,Category){"use strict";$scope.sidebarOpenedClass="",$scope.isLoggedIn=!1,$scope.fbUser=FacebookUser,Category.getAll().success(function(response){$scope.categories=response.data.categories}),Tag.getTop(5).success(function(response){$scope.tags=response.data.tags}),$scope.toggleSidebar=function(){$scope.sidebarOpenedClass=""===$scope.sidebarOpenedClass?"sidebar-opened":""},$scope.isActivePage=function(pageName){return pageName===$location.path()},$scope.login=function(){Facebook.login(function(response){$scope.getLoginStatus()},{})},$scope.logout=function(){Facebook.logout(function(response){FacebookUser.logout(),$scope.getLoginStatus()})},$scope.getLoginStatus=function(){Facebook.getLoginStatus(function(response){FacebookUser.setAccessToken(""),"connected"===response.status?(FacebookUser.setAccessToken(response.authResponse.accessToken),FacebookUser.setUserID(response.authResponse.userID),FacebookUser.login().success(function(response){$scope.isLoggedIn=!0}).error(function(response){$scope.isLoggedIn=!1})):$scope.isLoggedIn=!1})},$scope.searchConfessions=function(query){console.log("/search/"+query),$location.path("/search/"+query)},$scope.getLoginStatus()}),angular.module("nuswhispersApp.controllers").controller("SubmitController",function($scope,$http,Confession,Category,vcRecaptchaService){"use strict";Category.getAll().success(function(response){$scope.categories=response.data.categories}),$scope.confessionData={},$scope.form={imageSelected:!1,selectedCategoryIDs:[],errors:[],submitSuccess:!1},$scope.setRecaptchaResponse=function(response){$scope.confessionData.captcha=response},$scope.submitConfession=function(){$scope.confessionData.categories=$scope.form.selectedCategoryIDs,Confession.submit($scope.confessionData).success(function(response){if($scope.form.submitSuccess=response.success,!response.success){$scope.form.errors=[];for(var error in response.errors)for(var msg in response.errors[error])$scope.form.errors.push(response.errors[error][msg])}}).error(function(response){console.log(response)})},$scope.uploadConfessionImage=function(){filepicker.pick({extensions:[".png",".jpg",".jpeg"],container:"window"},function(fp){$scope.confessionData.image=fp.url,$scope.form.imageSelected=!0,$scope.$apply()},function(fpError){console.log(fpError.toString())})},$scope.toggleCategorySelection=function(category){var index=$scope.form.selectedCategoryIDs.indexOf(category.confession_category_id);index>-1?$scope.form.selectedCategoryIDs.splice(index,1):$scope.form.selectedCategoryIDs.push(category.confession_category_id)},$scope.highlightTags=function(){if($scope.contentTagHighlights="",void 0!==$scope.confessionData.content){var splitContentTags=$scope.confessionData.content.split(/(#\w+)/);for(var i in splitContentTags)$scope.contentTagHighlights+=/(#\w+)/.test(splitContentTags[i])?"<b>"+splitContentTags[i]+"</b>":splitContentTags[i];$scope.contentTagHighlights=$scope.contentTagHighlights.replace(/(?:\r\n|\r|\n)/g,"<br>")}}}),angular.module("nuswhispersApp.services").factory("Category",function($http){"use strict";return{getAll:function(){return $http.get("/api/categories")}}}),angular.module("nuswhispersApp.services").factory("Confession",function($http){"use strict";function Confession(confessionData){confessionData&&this.setData(confessionData)}return Confession.submit=function(confessionData){return $http({method:"POST",url:"/api/confessions",headers:{"Content-Type":"application/x-www-form-urlencoded"},data:$.param(confessionData)})},Confession.getConfessionById=function(confessionID){return $http({method:"GET",url:"/api/confessions/"+confessionID})},Confession.getFeatured=function(timestamp,offset,count){return $http({method:"GET",url:"/api/confessions",params:{timestamp:timestamp,offset:offset,count:count}})},Confession.getPopular=function(timestamp,offset,count){return $http({method:"GET",url:"/api/confessions/popular",params:{timestamp:timestamp,offset:offset,count:count}})},Confession.getRecent=function(timestamp,offset,count){return $http({method:"GET",url:"/api/confessions/recent",params:{timestamp:timestamp,offset:offset,count:count}})},Confession.getCategory=function(categoryID,timestamp,offset,count){return $http({method:"GET",url:"/api/confessions/category/"+categoryID,params:{timestamp:timestamp,offset:offset,count:count}})},Confession.getTag=function(tag,timestamp,offset,count){return $http({method:"GET",url:"/api/confessions/tag/"+tag,params:{timestamp:timestamp,offset:offset,count:count}})},Confession.search=function(query,timestamp,offset,count){return $http({method:"GET",url:"/api/confessions/search/"+escape(query),params:{timestamp:timestamp,offset:offset,count:count}})},Confession.getFavourites=function(timestamp,offset,count){return $http({method:"GET",url:"/api/confessions/favourites/",params:{timestamp:timestamp,offset:offset,count:count}})},Confession.prototype={setData:function(confessionData){angular.extend(this,confessionData)},load:function(){var confession=this;$http.get("/api/confessions/"+confession.confession_id).success(function(response){response.success&&confession.setData(response.data.confession)})},favourite:function(){return $http({method:"POST",url:"/api/fbuser/favourite",headers:{"Content-Type":"application/x-www-form-urlencoded"},data:$.param({confession_id:this.confession_id})})},unfavourite:function(){return $http({method:"POST",url:"/api/fbuser/unfavourite",headers:{"Content-Type":"application/x-www-form-urlencoded"},data:$.param({confession_id:this.confession_id})})}},Confession}),angular.module("nuswhispersApp.services").factory("FacebookUser",function($http){"use strict";var data={accessToken:"",userID:"",pageID:"1448006645491039"};return{login:function(){return $http({method:"POST",url:"/api/fbuser/login/",headers:{"Content-Type":"application/x-www-form-urlencoded"},data:$.param({fb_access_token:data.accessToken})})},logout:function(){return $http({method:"POST",url:"/api/fbuser/logout/",headers:{"Content-Type":"application/x-www-form-urlencoded"}})},setAccessToken:function(accessToken){data.accessToken=accessToken},getAccessToken:function(){return data.accessToken},setUserID:function(userID){data.userID=userID},getUserID:function(){return data.userID}}}),angular.module("nuswhispersApp.services").factory("Tag",function($http){"use strict";return{getTop:function(n){return $http.get("/api/tags/top/"+n)}}});