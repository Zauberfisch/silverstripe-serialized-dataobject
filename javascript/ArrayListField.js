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
			this.sortableEnable();
			this._super();
		},
		onunmatch: function () {
			this.sortableDisable();
			this._super();
		},
		sortableEnable: function () {
			// enable sorting functionality
			this.sortable({
				handle: ".orderable-handle",
				axis: "y"
			});
		},
		sortableDisable: function () {
			try {
				this.sortable("destroy");
			} catch (e) {
			}
		}
	});
	$('.zauberfisch\\\\SerializedDataObject\\\\Form\\\\ArrayListField .add-record').entwine({
		onclick: function () {
			var field = this.getContainerField(),
				recordList = field.getRecordList(),
				_this = this,
				newIndex = recordList.find('.record').length,
				url = field.data('add-record-url');
			this.addClass('loading');
			this.getRootForm().addClass('changed');
			$.get(url, {'index': newIndex}, function (content) {
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
	$('.zauberfisch\\\\SerializedDataObject\\\\Form\\\\ArrayListField .orderable-up, .zauberfisch\\\\SerializedDataObject\\\\Form\\\\ArrayListField .orderable-down').entwine({
		onclick: function () {
			var record = this.closest('.record'),
				recordList = this.getContainerField().getRecordList(),
				index = record.index();
			console.log(index);
			console.log(recordList.find('.record').length - 1);
			if (
				(index === 0 && this.hasClass('orderable-up')) ||
				(index === recordList.find('.record').length - 1 && this.hasClass('orderable-down'))
			) {
				return false;
			}
			recordList.sortableDisable();
			if (this.hasClass('orderable-up')) {
				record.prev().insertAfter(record);
			} else {
				record.next().insertBefore(record);
			}
			recordList.sortableEnable();
			this.blur();
			return false;
		}
	});
})
(jQuery);
