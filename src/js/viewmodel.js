function ViewModel() {
	let self = this;
	self.dataReady = ko.observable(false);
	self.messages = ko.observable('Fetching...');
	self.files = ko.observableArray([]);
	

    self.GenerateDatasetUrl = function () {
        if (location.href.startsWith('file'))
            return 'data/demo.json';

        return 'api/index.php';
    };
    
	self.fetchData = function (url) {
		return new Promise((resolve, reject) => {
			$.ajax({
				type: 'GET',
				url: url,
				dataType: 'json',
				mimeType: 'application/json',
				success: function (data) {
					resolve(data);
				},
				error: function (error) {
					reject(error);
				}
			});
		});
	};

	self.Init = function () {
        self.fetchData(self.GenerateDatasetUrl())
        .then((files) => {
            self.files = files;
            ko.applyBindings(self);
            self.dataReady(true);
        });
	};
}

let vm = new ViewModel();
vm.Init();