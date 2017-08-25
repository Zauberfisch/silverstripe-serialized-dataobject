(function ($) {
	$('.zauberfisch\\\\SerializedDataObject\\\\Form\\\\ArrayListField').entwine({
		getRecordList: function () {
			return this.find('.record-list');
		}
	});
	$('.zauberfisch\\\\SerializedDataObject\\\\Form\\\\ArrayListField *').entwine({
		getRootForm: function () {
			return this.closest('form');
		},
		getContainerField: function () {
			return this.closest('.zauberfisch\\\\SerializedDataObject\\\\Form\\\\ArrayListField');
		}
	});
	$('.zauberfisch\\\\SerializedDataObject\\\\Form\\\\ArrayListField.orderable .record-list').entwine({
		onmatch: function () {
			// enable sorting functionality
			var self = this,
				rootForm = this.closest('form');
			self.sortable({
				handle: ".orderable-handle",
				axis: "y"
			});
			this._super();
		},
		onunmatch: function () {
			try {
				$(this).sortable("destroy");
			} catch (e) {
			}
			this._super();
		}
	});
	$('.zauberfisch\\\\SerializedDataObject\\\\Form\\\\ArrayListField .add-record').entwine({
		onclick: function () {
			var field = this.getContainerField(),
				recordList = field.getRecordList(),
				_this = this,
				newIndex = recordList.find('.record').length,
				url = field.data('add-record-url') + '?index=' + newIndex;
			this.addClass('loading');
			this.getRootForm().addClass('changed');
			$.get(url, function (content) {
				recordList.append(content);
				_this.removeClass('loading');
				_this.blur();
			});
			return false;
		}
	});
	$('.zauberfisch\\\\SerializedDataObject\\\\Form\\\\ArrayListField .delete-record').entwine({
		onclick: function () {
			var container = this.closest('.record'),
				_this = this;
			container.addClass('pre-delete');
			setTimeout(function () {
				if (confirm(_this.data('confirm'))) {
					container.fadeOut(function () {
						container.remove();
					});
				}
				container.removeClass('pre-delete');
			}, 100);
			this.blur();
			return false;
		}
	});
})
(jQuery);
