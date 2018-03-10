angular.module('image.basics')

.controller('CanvasBackgroundController', ['$scope', '$rootScope', 'canvas', 'history', function($scope, $rootScope, canvas, history) {

    //canvas.fabric.setBackgroundColor('#ffffff');
    //canvas.fabric.renderAll();
    $scope.setBackground = function(color) {
        canvas.fabric.setBackgroundColor(color);
        canvas.fabric.renderAll();
    };

    $scope.apply = function() {
        $rootScope.activePanel = false;
        $rootScope.bgColor = $("#cvs-bgcolor").val();
        history.add('Canvas Color', 'format-color-fill');
    };

    $scope.cancel = function() {
        $rootScope.activePanel = false;
        canvas.fabric.setBackgroundColor($rootScope.bgColor);
        canvas.fabric.renderAll();
    };
}]);