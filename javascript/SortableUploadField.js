;(function($) {
	$(function(){
		$.entwine('ss', function($) {
			$(".zauberfisch\\\\serializeddataobject\\\\form\\\\sortableupload.ss-uploadfield ul.ss-uploadfield-files").entwine({
				onmatch: function() {
					// enable sorting functionality
					var self = this,
						rootForm = this.closest('form');
					self.sortable({
						handle: ".ss-uploadfield-item-preview",
						axis: "y",
						start: function(event, ui){
							// remove overflow on container
							ui.item.data("oldPosition", ui.item.index());
							self.css("overflow", "hidden");
						},
						stop: function(event, ui){
							// restore overflow
							self.css("overflow", "auto");
							//rootForm.addClass('changed');
						}
					});
					this._super();
				},
				onunmatch: function(){
					// clean up
					try {
						$(this).sortable("destroy");
					} catch(e){}
					this._super();
				}
			});
		});
	});
}(jQuery));
