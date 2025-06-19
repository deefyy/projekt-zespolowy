<x-app-layout>
    <x-slot name="header">
        <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-[#002d62] text-center">
                    {{ __('Import Submissions (Step 1/2): Upload File') }}
                </h2>
            </div>
        </header>
    </x-slot>

    <div class="py-12 bg-[#f9fbfd] min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-[#cdd7e4] shadow rounded-2xl p-6">
                
                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <p class="mb-6 text-sm text-gray-700">
                    {{ __('Select an Excel file to import. In the next step, the system will analyze the column headers and allow you to match them.') }}
                </p>

                <form action="{{ route('competitions.handleImportUpload', $competition) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-6">
                        <label for="excel_file" class="block text-sm font-medium text-gray-900 mb-2">
                            {{ __('Excel file (.xlsx, .xls, .csv):') }}
                        </label>
                        <input type="file" name="excel_file" id="excel_file" required
                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#002d62] file:bg-[#002d62] file:text-white file:rounded-md file:border-0 file:px-4 file:py-2 hover:file:bg-[#001a40] transition">
                        @error('excel_file')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="text-right">
                        <button type="submit"
                                class="bg-[#0073cf] hover:bg-[#005999] text-white font-semibold py-2 px-6 rounded-xl shadow transition">
                            {{ __('Proceed to file analysis') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
