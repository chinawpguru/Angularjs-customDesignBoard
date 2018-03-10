'use strict';

angular.module('ImageEditor')

.factory('saver', ['$rootScope', '$mdDialog', '$http', '$timeout', 'canvas', 'cropper', 'history', function($rootScope, $mdDialog, $http, $timeout, canvas, cropper, history) {

	var saver = {

        saveImage: function(format, quality, name, e, close) {

            canvas.fabric.deactivateAll();
            cropper.stop();

            if ($rootScope.isDemo) {
                return this.handleDemoSiteSave(e);
            }

            if ($rootScope.isIntegrationMode) {
                return this.handleIntegrationModeSave(format, quality, name);
            }

            //this.saveToComputer(format, quality, name);
            this.saveToServer(name, close);
        },

        handleIntegrationModeSave: function(format, quality, name) {
            canvas.zoom(150/canvas.original.height);
            var data = this.getDataUrl(format, quality);

            this.handleCallbacks(data, name);

            //firefox integration mode fix
            $('.md-dialog-container').remove();
            
            $mdDialog.hide();
            $rootScope.pixie.close();
            canvas.zoom(0.5);
        },

        saveToServer: function(name, close) {

            canvas.zoom(150/canvas.original.height);
            var data_jpeg = this.getDataUrl('jpeg', 8);

            canvas.zoom(1);            
            var data_png = this.getDataUrl('png', 8),
                data_json = this.getDataUrl('json', 8);
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
                type: 'POST',
                success: function(res){

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
            $mdDialog.hide();
        },

        saveToComputer: function(format, quality, name) {
            canvas.zoom(1);

            var link = document.createElement('a'),
                data = this.getDataUrl(format, quality);

            this.handleCallbacks(data, name);

            //browser supports html5 download attribute
            if (typeof link.download !== 'undefined') {
                link.download = (name || 'image')+'.'+format;
                link.href = data;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            //canvas blob and file saver workaround
            else {
                canvas.fabric.lowerCanvasEl.toBlob(function(blob) {
                    saveAs(blob, name+'.'+format);
                }, 'image/'+format, quality);
            }

            $mdDialog.hide();
        },

        handleCallbacks: function(data, name) {
            //replace image src with new data url in original window
            if ($rootScope.getParam('replaceOriginal') && $rootScope.getParam('image')) {
                $rootScope.getParam('image').src = data;
            }

            //send image data to user specified url
            if ($rootScope.getParam('saveUrl')) {
                $http.post($rootScope.getParam('saveUrl'), { data: data, name: name });
            }

            if ($rootScope.getParam('onSave')) {
                var img = $rootScope.getParam('image') || new Image(data);
                $rootScope.getParam('onSave')(data, img, name);
            }
        },

        handleDemoSiteSave: function(e) {
            $('.demo-alert').one($rootScope.animationEndEvent, function() {
                $(this).removeClass('animated shake');  e.target.blur();
            }).addClass('animated shake');
        },

        getDataUrl: function(format, quality) {
            if (format === 'json') {
                return "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(history.getCurrentCanvasState()));
            }
            return canvas.fabric.toDataURL({
                format: format || 'png',
                quality: (quality || 8) / 10
            });
        },

        getThumbDataUrl: function(datas, wantedWidth, wantedHeight){
            // We create an image to receive the Data URI
            var img = document.createElement('img');

            // We put the Data URI in the image's src attribute
            img.src = datas;
            var dataURI = '';
            // When the event "onload" is triggered we can resize the image.
            img.onload = function()
                {        
                    // We create a canvas and get its context.
                    var canvas = document.createElement('canvas');
                    var ctx = canvas.getContext('2d');

                    // We set the dimensions at the wanted size.
                    canvas.width = wantedWidth;
                    canvas.height = wantedHeight;

                    // We resize the image with the canvas method drawImage();
                    ctx.drawImage(this, 0, 0, wantedWidth, wantedHeight);

                    dataURI = canvas.toDataURL();
                    /////////////////////////////////////////
                    // Use and treat your Data URI here !! //
                    /////////////////////////////////////////
                };
            while(dataURI == ''){};
            return dataURI;
        }
	};

	return saver;
}]);