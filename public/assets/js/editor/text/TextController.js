angular.module('image.text')

.controller('TextController', ['$scope', '$rootScope', '$timeout', 'canvas', 'text', 'fonts', function($scope, $rootScope, $timeout, canvas, text, fonts) {
	$scope.text  = text;
	$scope.fonts = fonts;

	$scope.activeFont = '';
	$scope.opacity = 1;
	$scope.fontSize = 25;
	$scope.txtColor = '#000000';
	$scope.enableBackground = true;
	$scope.txtBgColor = 'rgba(0,0,0,0)';
	$scope.enableOutline = true;
	$scope.txtOutlineColor = 'rgba(0,0,0,0)';
	$scope.txtAlign = 'left';
	$scope.txtUnderline = '';
	$scope.txtStyle = '';
	$scope.txtLineHeight = '';
	$scope.txtOutlineWidth = '';
	$scope.filters = {
		category: 'handwriting',
		family: ''
	};

	fonts.getAll($scope.filters);
    $scope.isPanelEnabled = function() {
        var obj = canvas.fabric.getActiveObject();
        $rootScope.activeObject = obj;
        if(obj && obj.name === 'text') $scope.setTxtValue(obj);
        return obj && obj.name === 'text' && $rootScope.activeTab === 'text';
    };

    $scope.setTxtValue = function(obj){
    	console.log(obj);
    	$scope.activeFont = obj.fontFamily;
	   	$scope.opacity = obj.opacity;
		$scope.fontSize = obj.fontSize;
		$scope.txtColor = obj.fill;
		if(obj.backgroundColor) {
			$scope.enableBackground = true;
			$scope.txtBgColor = obj.backgroundColor;
		}else{
			$scope.enableBackground = false;
		}
		if(obj.stroke) {
			$scope.enableOutline = true;
			$scope.txtOutlineColor = obj.stroke; 	
		}else{
			$scope.enableOutline = false;
		}
		$scope.txtAlign = obj.textAlign;
		console.log($scope.txtAlign);
		$scope.txtUnderline = obj.textDecoration;
		$scope.txtStyle = obj.fontStyle;
		$scope.txtLineHeight = obj.lineHight;
		$scope.txtOutlineWidth = obj.strokeWidth;
		jQuery("#txtColor").spectrum("set", obj.fill);
		if($scope.enableBackground) jQuery("#txtBgColor").spectrum("set", $scope.txtBgColor);
		if($scope.enableOutline) jQuery("#txtOutlineColor").spectrum("set", $scope.txtOutlineColor);
    };

	$scope.changeFont = function(font, e) {
        var active = canvas.fabric.getActiveObject();
		$rootScope.openPanel('text', e);

		if ( ! active || active.name !== 'text') {
			var newText = new fabric.IText('Double click me to edit my contents.', {
				fontFamily: font,
				fontWeight: 400,
				fontSize: $scope.fontSize,
				fill: $scope.txtColor,
				removeOnCancel: true,
				name: 'text'
			});

			canvas.fabric.add(newText);
            newText.setTop(25);
            newText.setLeft(25);
			canvas.fabric.setActiveObject(newText);
			canvas.fabric.renderAll();
		}

        text.setProperty('fontFamily', font, true);
		$scope.setTxtValue(active);
		$scope.activeFont = font;
	};

	$scope.cancelAddingTextToCanvas = function() {
		var textObject = text.getTextObject();

		if (textObject.removeOnCancel) {
			text.removeTextFromCanvas(textObject);
		}

		$rootScope.activePanel = false;
	};

	$scope.finishAddingTextToCanvas = function() {
		var textObject = text.getTextObject();

		$rootScope.activePanel = false;
		$rootScope.$emit('text.added', textObject);
		canvas.fabric.setActiveObject(canvas.mainImage);
		fonts.createLinkToFont(textObject.fontFamily);
		$rootScope.activeObject = canvas.mainImage;
	};

	$rootScope.$on('tab.changed', function(e, name) {
		if (name == 'text') {
			var textObject = $scope.text.getTextObject();

			//if we can find an existing text object set it as active
			if (textObject) {
				textObject.removeOnCancel = false;
				canvas.fabric.setActiveObject(textObject);
			}
		}
	});

}]);