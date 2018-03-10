angular.module('image.shapes')

.directive('edPhotosCategories', function() {
    return {
        restrict: 'A',
        link: function($scope, el) {

            //load photo images into open category when photos tab is opened
            var unbind = $scope.$watch('activeTab', function(newTab) {
                if (newTab !== 'photos') return;

                var cat = $('.category-photos.open');

                cat.find('img').each(function() {
                    this.src = this.dataset.src;
                });

                cat.parent().addClass('loaded');

                unbind();
            });

            //load photo images for the category that just been opened
            el.on('click', '.category-header', function(e) {
                $scope.$apply(function() {
                    $scope.setActivePhotoCategory(e.target.dataset.name);
                });

                var category = $(e.currentTarget).parent();

                if ( ! category.hasClass('loaded')) {
                    category.addClass('loaded').find('img').each(function() {
                        this.src = this.dataset.src;
                    });
                }
            });
        }
   	}
});