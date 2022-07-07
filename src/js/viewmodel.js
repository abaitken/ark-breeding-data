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
        .then((data) => {

			let files = [];
			for(let i = 0; i < data.length; i ++) {
				files.push(new FileViewModel(data[i]));
			}

            self.files(files);
            ko.applyBindings(self);
            self.dataReady(true);
        });
	};
}

function FileViewModel(item) {
	let self = this;
	self.text = item['text'];
	self.filename = item['filename'];
	self.url = item['url'];
	self.filesize = item['filesize'];
	self.modified = new Date(item['modified']).toLocaleString();
}

let vm = new ViewModel();
vm.Init();