'use strict';

angular.module('ImageEditor')

.controller('TopPanelController', ['$rootScope', '$scope', '$mdDialog', '$mdToast', '$$rAF', '$timeout', '$window', 'canvas', 'history', 'saver', function($rootScope, $scope, $mdDialog, $mdToast, $$rAF, $timeout, $window, canvas, history, saver) {

    $scope.history = history;

    $scope.isDemo = $rootScope.isDemo;
    $scope.canOpenImage = false;
    $scope.canvas = canvas;
    $scope.openImageMode = 'open';

    $scope.canvasWidth = 800;
    $scope.canvasHeight = 600;

    $scope.imageName = 'image';
    $scope.imageType = 'jpeg';
    $scope.imageQuality = 8;
    
    $scope.imageName = $rootScope.order_id + "-" + $rootScope.product_id;
    $scope.objectsPanelOpen = true;
    $scope.historyPanelOpen = false;

    $scope.openUploadDialog = function($event) {
        $mdDialog.show({
            template: $('#main-image-upload-dialog-template').html(),
            targetEvent: $event,
            controller: 'TopPanelController',
            clickOutsideToClose: true,
        });
    };

    $scope.toggleRightPanel = function(name, e) {
        var panelIsOpen = $scope[name+'PanelOpen'];

        if (panelIsOpen) {
            $scope[name+'PanelOpen'] = false;
            $('#'+name).hide();
        } else {
            $scope[name+'PanelOpen'] = true;
            $('#'+name).show();
        }
    };

    $scope.transformOpen = function(name, e) {
        var panel = $('#'+name);

        panel.removeClass('transition-out transition-in').show();
        $scope.transformToClickElement(panel, e);

        $$rAF(function() {
            panel.addClass('transition-in').css('transform', '');
            e.currentTarget.blur();
        });
    };

    $scope.transformClose = function(name, e) {
        var panel = $('#'+name);

        panel.addClass('transition-out').removeClass('transition-in');
        $scope.transformToClickElement(panel, e);

        panel.one($rootScope.transitionEndEvent, function() {
            panel.hide().css('transform', '').removeClass('transition-out');
            e.currentTarget.blur();
        });
    };

    $scope.transformToClickElement = function(panel, e) {
        var clickRect = e.target.getBoundingClientRect();
        var panelRect = panel[0].getBoundingClientRect();

        var scaleX = Math.min(0.5, clickRect.width / panelRect.width);
        var scaleY = Math.min(0.5, clickRect.height / panelRect.height);

        panel.css('transform', 'translate3d(' +
            (-panelRect.left + clickRect.left + clickRect.width/2 - panelRect.width/2) + 'px,' +
            (-panelRect.top + clickRect.top + clickRect.height/2 - panelRect.height/2) + 'px,' +
            '0) scale(' + scaleX + ',' + scaleY + ')'
        );
    };

    $scope.openSaveDialog = function($event) {
        if ($rootScope.getParam('onSaveButtonClick')) {
            return $rootScope.getParam('onSaveButtonClick')();
        }

        if ($rootScope.delayEditorStart) {
            return saver.saveImage();
        }

        $mdDialog.show({
            template: $('#save-image-dialog').html(),
            targetEvent: $event,
            controller: 'TopPanelController',
            clickOutsideToClose: true,
        });
    };

    $scope.openHelp = function($event) {

        $mdDialog.show({
            template: $('#modal-help-text').html(),
            clickOutsideToClose: true,
            controller: ['$scope', '$mdDialog', function($scope, $mdDialog) {
                $scope.closeModal = $mdDialog.hide;
            }]
        });        
    };

    $scope.createNewCanvas = function(width, height) {
        canvas.openNew(width, height);
        $scope.closeUploadDialog();
        $rootScope.started = true;
        $rootScope.resetUI(); 
    };

    $scope.openSampleImage = function() {
        canvas.loadMainImage('assets/images/lotus.jpg');
        $scope.closeUploadDialog();
        $rootScope.started = true;
    };

    $scope.saveImage = function($event) {
        saver.saveImage($scope.imageType, $scope.imageQuality, $scope.imageName, $event, false);
        //$scope.saveToServer($scope.imageName, false);
    };

    $scope.saveImageClose = function($event) {
        saver.saveImage($scope.imageType, $scope.imageQuality, $scope.imageName, $event, true);
        // $timeout(function() {
        //     top.tb_remove();
        // }, 1000);
    };

    $scope.saveToServer = function(name, close){
        canvas.fabric.deactivateAll();
        //cropper.stop();

        canvas.zoom(150/canvas.original.height);
        var data_jpeg = saver.getDataUrl('jpeg', 8);

        canvas.zoom(1);            
        var data_png = saver.getDataUrl('png', 8),
            data_json = saver.getDataUrl('json', 8);
        canvas.fitToScreen();  
        $rootScope.isLoading();
        jQuery.ajax({
            url: "/wp-admin/admin-ajax.php",
            data: {
                action: 'wineshop_label_save',
                order_id: $rootScope.order_id,
                product_id: $rootScope.product_id,
                jpeg : data_jpeg,
                png : data_png,
                json : data_json
            },
            async: false,
            type: 'POST',
            success: function(res){
                console.log($rootScope.loading);
                $rootScope.isNotLoading();
                if(close){
                    $timeout(function() {

                        if (typeof top.tb_remove == 'function') {
                            top.tb_remove();
                        }

                        var event;
                        var closeEventName = "personalized-wine-designer-modal-close";
                        if (document.createEvent) {
                            event = document.createEvent("HTMLEvents");
                            event.initEvent(closeEventName, true, true);
                        } else {
                            event = document.createEventObject();
                            event.eventType = closeEventName;
                        }

                        event.eventName = closeEventName;

                        if (document.createEvent) {
                            document.dispatchEvent(event);
                            window.parent.document.dispatchEvent(event);
                        } else {
                            document.fireEvent("on" + event.eventType, event);
                            window.parent.document.fireEvent("on" + event.eventType, event);
                        }

                    }, 1000);
                }
            }
        })
    }

    $scope.showImagePreview = function(url) {
        var historyObject = false;

        try {
            historyObject = JSON.parse(url);
        } catch (e) {
            //
        }

        if (historyObject && historyObject.state) {
            $scope.$apply(function() {
                $scope.canOpenImage = false;
            });

            canvas.fabric.clear();
            history.load(historyObject);
            $scope.closeUploadDialog();
            $rootScope.started = true;
            return;
        }

        fabric.util.loadImage(url, function(image) {
            if (image) {
                $scope.$apply(function() {
                    $('.img-preview').html('').append(image);
                    $scope.canOpenImage = true;
                });
            } else {
                $scope.$apply(function() {
                    $scope.canOpenImage = false;
                });
            }
        });
    };

    $scope.openImage = function() {
        var url = $('.img-preview img').attr('src');
            
        if ( ! url || ! $scope.canOpenImage) return;

        if ((!canvas.fabric._objects.length || ! canvas.mainImage) && ! $rootScope.userPreset) {
            canvas.fabric.clear();
            canvas.loadMainImage(url);
        } else {
            canvas.openImage(url);
        }

        $scope.closeUploadDialog();
        $rootScope.started = true;
    };

    $scope.closeUploadDialog = function() {
        $scope.canUploadImage = false;
        $scope.openImageMode = 'open';
        $('.img-preview').html();
        $mdDialog.hide();
    };
}]);



