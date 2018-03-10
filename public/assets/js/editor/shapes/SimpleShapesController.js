angular.module('image.shapes', [])

.controller('SimpleShapesController', ['$scope', '$rootScope', '$timeout', 'canvas', 'simpleShapes', function($scope, $rootScope, $timeout, canvas, simpleShapes) {
	$scope.shapes = simpleShapes;

    $scope.available = ['rect', 'triangle', 'circle', 'ellipse', 'polygon'];

    $scope.isPanelEnabled = function() {
        var obj = canvas.fabric.getActiveObject();
        if(obj && $scope.available.indexOf(obj.name) > -1){
            $scope.setSeletedShape(obj);
            $rootScope.activeObject = obj;
        }
        return obj && $scope.available.indexOf(obj.name) > -1 && simpleShapes.selected.options;
    };

    $scope.setSeletedShape = function(obj){

        $scope.shapes.selectShape(obj.name);
        $scope.shapes.selectedShape = obj;

        var selectedShape = $scope.shapes.selected;

        selectedShape.options.main.fill.current = obj.fill;
        selectedShape.options.main.opacity.current = obj.opacity;
        if(obj.name == 'circle'){
            selectedShape.options.main.radius.current = obj.radius;
        }else if(obj.name == 'ellipse'){
            selectedShape.options.main.rx.current = obj.rx;
            selectedShape.options.main.ry.current = obj.ry;
        }else if(obj.name != 'polygon'){
            selectedShape.options.main.width.current = obj.width;
            selectedShape.options.main.height.current = obj.height;
        }
        jQuery("#shapeBgColor").spectrum("set", obj.fill);
        if(obj.stroke){
            selectedShape.options.border.enabled = true;
            selectedShape.options.border.stroke.current = obj.stroke;
            selectedShape.options.border.strokeWidth.current = obj.strokeWidth;
            jQuery("#borderstroke").spectrum("set", obj.stroke);
        }else{
            selectedShape.options.border.enabled = false;
        }
        if(obj.shadow){
            selectedShape.options.shadow.enabled = true;
            selectedShape.options.shadow.color.current = obj.shadow.color;
            selectedShape.options.shadow.blur.current = obj.shadow.blur;
            selectedShape.options.shadow.offsetX.current = obj.shadow.offsetX;
            selectedShape.options.shadow.offsetY.current = obj.shadow.offsetY;
            jQuery("#shadowcolor").spectrum("set", obj.shadow.color);
        }else{
            selectedShape.options.shadow.enabled = false;
        }

        // $scope.selectedShape['height'] = obj.height;
        // $scope.selectedShape['opacity'] = obj.opacity;
        // $scope.selectedShape['width'] = obj.width;
        // $scope.selectedShape['strokeWidth'] = obj.strokeWidth;
        // $scope.selectedShape['stroke'] = obj.stroke;
        // $scope.selectedShape['shadow'] = obj.shadow;

    };

    // canvas.fabric.on('object:selected', function(object) {
    //     if ($rootScope.activeTab !== 'simple-shapes') return;
    //     simpleShapes.selectShape(object.target.name);
    // });
}]);