'use strict';

angular.module('ImageEditor')

.controller('PhotoController', ['$rootScope', '$scope', '$mdDialog', '$mdToast', '$$rAF', 'canvas', 'history', 'settings', 'saver', function($rootScope, $scope, $mdDialog, $mdToast, $$rAF, canvas, history, settings, saver) {

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
    $scope.photoPath = '';

    $scope.objectsPanelOpen = true;
    $scope.historyPanelOpen = false;

    $rootScope.$on('settings.ready', function() {
        var photos = settings.get('photos').photos;
        //var images = photos.images.items;

        // for (var cat in photos) {
        //     photos[cat].items = new Array(photos[cat].length);
        // };

        $scope.photos = photos;
    });

    $scope.activePhotoCategory = 'user';

    $scope.setActivePhotoCategory = function(name) {
        if ($scope.activePhotoCategory === name) {
            $scope.activePhotoCategory = false;
        } else {
            $scope.activePhotoCategory = name;
        }
    };

    $scope.openUploadDialog = function($event) {
        $mdDialog.show({
            template: $('#main-image-upload-dialog-template').html(),
            targetEvent: $event,
            controller: 'PhotoController',
            clickOutsideToClose: true,
        });
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


    $scope.openSampleImage = function() {
        canvas.loadMainImage('assets/images/lotus.jpg');
        $scope.closeUploadDialog();
        $rootScope.started = true;
    };


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


    $scope.addToCanvas = function(category, index, e) {
        if ($scope.loading) return;
        $scope.loading = true;
        //$scope.openPanel('photos', e);
        var img_url = category.path + category.items[index].image;
        // var http = new XMLHttpRequest();
        // http.open('HEAD', img_url, false);
        // http.send();
        // if(http.status==404) img_url = 'assets/images/photos/'+category.name+'/'+index+'.'+category.type;
        fabric.util.loadImage( img_url, function(img) {
            var image = new fabric.Image(img);
            afterAddPhotoToCanvas(image);
        });
        canvas.fitToScreen(); 
    }

    function afterAddPhotoToCanvas(image) {
        image.name = 'photo';
        canvas.fabric.add(image);
        image.scaleToHeight((50 / 100) * canvas.original.height);

        image.center();
        image.setCoords();
        canvas.fabric.setActiveObject(image);
        canvas.fabric.renderAll();
        $rootScope.activeObject = image;

        $scope.$apply(function() {
            $scope.loading = false;
        });

        history.add('Added: Photo', 'favorite');
    }

}]);



