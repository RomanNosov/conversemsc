import $ from "jquery";
// import * as _ from "./sugar";

import angular from "angular";
import ngBootstrap from "angular-ui-bootstrap";

var appRoot = "/wa-apps/taskmanager/",
	appUri  = "?module=backend&action=api";

var TaskmanagerApp = angular.module("taskmanagerApp", [ngBootstrap]);

TaskmanagerApp.config(($httpProvider) => {
	$httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    $httpProvider.defaults.transformRequest = (data) => {
        return angular.isObject( data ) && String( data ) !== '[object File]' ? $.param( data ) : data;
    };
});

TaskmanagerApp.directive("taskmanagerApp", () => ({
        restrict: 'A',
        templateUrl: `${appRoot}templates/actions/frontend/index.html`,
        scope: {},
        controller: ($scope, $modal, $http, $interval, $sce) => {

        	document.onscroll = (e) => {
        		if ((document.body.scrollHeight - document.body.offsetHeight - document.body.scrollTop) < 100){
					$scope.setCurrentTab($scope.currentTab, ++$scope.currentPage);
				}
        	};

        	$scope.trustAsHtml = $sce.trustAsHtml;

        	$scope.currentTab = 0;
        	$scope.currentPage = 0;

        	$scope.users = [];
        	$scope.currentUser = {};

        	$scope.newTasks = [];
        	$scope.processingTasks = [];
        	$scope.doneTasks = [];
        	$scope.myTasks = [];

        	$scope.myTasksCount = 0;

        	$http
		        .post(appUri, { 
		        	type: "users"
		        })
		        .success(({ data : { users, currentUser } }) => {
		        	$scope.currentUser = currentUser;
		        	Object.keys(users).forEach((id) => {
		        		$scope.users.push(users[id]);
		        	});
		        });

        	$scope.setCurrentTab = (tab, page = 0) => {

        		$scope.currentTab = tab;
        		$scope.currentPage = page;

        		if (!page) {
        			window.scrollTo(0, 0);
        		}

        		switch (tab) {

					case 0:
						$http
					        .post(appUri, { 
					        	type: "tasks",
					        	status: "new",
					        	page: page
					        })
					        .success(({ data }) => {

					        	if (page == 0) {
					        		$scope.newTasks = data;
					        	} else 
					        	if (data.length) {
					        		$scope.newTasks.push(...data);
					        	}
					        });

						break;

					case 1:
						$http
					        .post(appUri, { 
					        	type: "tasks",
					        	status: "processing",
					        	page: page
					        })
					        .success(({ data }) => {

					        	if (page == 0) {
					        		$scope.processingTasks = data;
					        	} else 
					        	if (data.length) {
					        		$scope.processingTasks.push(...data);
					        	}
					        });

						break;

					case 2:
						$http
					        .post(appUri, { 
					        	type: "tasks",
					        	status: "done",
					        	page: page
					        })
					        .success(({ data }) => {

					        	if (page == 0) {
					        		$scope.doneTasks = data;
					        	} else 
					        	if (data.length) {
					        		$scope.doneTasks.push(...data);
					        	}
					        });

						break;

					case 3:
						$http
					        .post(appUri, { 
					        	type: "tasks",
					        	status: "my",
					        	page: page
					        })
					        .success(({ data }) => {

					        	if (page == 0) {
					        		$scope.myTasks = data;
					        	} else 
					        	if (data.length) {
					        		$scope.myTasks.push(...data);
					        	}
					        });

						break;
        		}
        	};

        	$scope.setTaskStatus = (status, task, tasks) => {
        		$http
			        .post(appUri, { 
			        	type: "setTaskStatus",
			        	id: task.id,
			        	status: status
			        })
			        .success(() => {
			        	tasks.splice(tasks.indexOf(task), 1);
			        });
        	};
            
            $scope.appoint = (title = "") => {

                $modal.open({
                    animation: true,
                    templateUrl: 'appoint.html',
                    controller: 'AppointModalController',
                    resolve: {
                        users: () => $scope.users,
                        title: () => title
                    }
                }).result.then(([title, to, deadline, text]) => {
					console.log(title, to, text);     
					$http
				        .post(appUri, { 
				        	type: "appoint",
				        	title,
				        	to,
				        	deadline,
				        	text
				        })
				        .success(({ data }) => {
				        	$scope.myTasks.unshift(data);
				        });               
                });
            };

            $scope.setCurrentTab(0);

            $interval(() => {

            	$http
			        .post(appUri, { 
			        	type: "myTasksCount"
			        })
			        .success(({ data }) => {
			        	$scope.myTasksCount = data;
			        });
            }, 500);


        	if (/\#\d+/.test(location.hash)) {
        		var id = location.hash.replace("#", "");
        		location.hash = "";
        		$scope.appoint(`Заказ #${id}`);
        	}
            
        }
}));

TaskmanagerApp.controller("AppointModalController", ($scope, $modalInstance, $sce, users, title) => {

	$scope.trustAsHtml = $sce.trustAsHtml;

    $scope.users = users;
    $scope.title = title;

    $scope.minDate = new Date();
    $scope.dateOptions = {
		formatYear: 'yy',
		startingDay: 1
	};

    $scope.ok = () => {
        $modalInstance.close([$scope.title, $scope.to, $scope.deadline ? `${$scope.deadline.getFullYear()}-${$scope.deadline.getMonth() + 1}-${$scope.deadline.getDate()}` : "", $scope.text]);
    };

    $scope.cancel = () => {
        $modalInstance.dismiss('cancel');
    };

});

window.$ = window.jQuery = $;