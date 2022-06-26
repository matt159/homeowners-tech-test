<template>
    <div class="wrapper w-full max-w-2xl mx-auto px-4 md:px-0">
        
        <div class="container relative mt-12 mb-12 p-4 pointer-events-auto rounded-lg bg-white leading-5 text-slate-700 shadow-xl shadow-black/5 ring-1 ring-slate-700/10">
            
            <div class="upload-area relative bg-blue-100 rounded-md border border-blue-200 border-dashed py-12 px-4 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-12 w-12 mb-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <div>
                    <div class="inline-block pointer-events-auto rounded-md bg-blue-600 py-2 px-4 text-xs font-semibold leading-5 text-white hover:bg-blue-800">Upload CSV File</div>
                    <span class="mt-4 text-sm text-slate-600" :class="{'block': filename}">{{ filename }}</span>
                </div>
                <input id="dropzone-file" type="file" v-on:change="fileChanged" class="opacity-0 absolute w-full h-full top-0 left-0" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"/>
            </div>

            <div class="loader text-center mb-4 mt-8" v-show="serverResponse.loading">
                <svg role="status" class="w-8 h-8 inline-block text-gray-200 animate-spin fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                </svg>
            </div>

            <span class="block text-red-500 text-center mt-4" v-show="serverResponse.error">{{ serverResponse.error }}</span>
            
            <div class="mt-4" v-show="serverResponse.data">
                <button v-on:click="downloadCSV" class="inline-block pointer-events-auto mb-2 rounded-md bg-blue-600 py-2 px-4 text-xs font-semibold leading-5 text-white hover:bg-blue-800">Download Cleaned CSV</button>
                <div class="rounded-md overflow-x-auto border border-gray-200">
                    <table class="r w-full text-sm ">
                        <thead class="bg-gray-100 text-slate-800 font-medium">
                            <tr class="border-gray-200 border-b">
                                <td class="px-4 py-3">Title</td>
                                <td class="px-4 py-3">Initial</td>
                                <td class="px-4 py-3">First Name</td>
                                <td class="px-4 py-3">Last Name</td>
                            </tr>
                        </thead>
                        <tbody class="text-slate-500 font-light">
                            <tr v-for="(entry, index) in serverResponse.data" :key="index" class="border-gray-200 border-b last:border-0">
                                <td class="px-4 py-3">{{ entry.title }}</td>
                                <td class="px-4 py-2">{{ entry.initial }}</td>
                                <td class="px-4 py-3">{{ entry.first_name }}</td>
                                <td class="px-4 py-3">{{ entry.last_name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>


        </div>

    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data() {
            return {
                file: null,
                filename: null,
                serverResponse: {
                    loading: false,
                    data: null,
                    error: null
                },
            }
        },
        methods: {
            fileChanged: function(e){
                this.filename = null;

                this.file = e.target.files[0];
                this.filename = this.file.name;

                this.processFile();
            },
            processFile: function(){
                this.serverResponse.data = null;
                this.serverResponse.error = null;
                this.serverResponse.loading = true;

                const config = {
                    headers: {
                        'content-type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                }
                
                let formData = new FormData();
                formData.append('file', this.file);
                
                let self = this;
                axios.post('/api/clean-csv', formData, config)
                    .then(function (response) {
                        self.serverResponse.data = response.data;
                        self.serverResponse.loading = false;
                    })
                    .catch(function (error) {
                        self.serverResponse.error = error;
                        self.serverResponse.loading = false;
                    });
            },
            downloadCSV: function(){
                let rows = [['Title', 'Initial', 'First Name', 'Last Name']];
                this.serverResponse.data.forEach(function(entry){
                    rows.push([entry.title, entry.initial, entry.first_name, entry.last_name]);
                })

                let csvContent = "data:text/csv;charset=utf-8," 
                    + rows.map(e => e.join(",")).join("\n");

                let encodedUri = encodeURI(csvContent);
                let link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", this.filename + "-cleaned.csv");
                document.body.appendChild(link); // Required for FF
                link.click();

            }
        }
    }
</script>
