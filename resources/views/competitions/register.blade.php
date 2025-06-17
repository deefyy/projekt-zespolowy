<x-app-layout>
  <x-slot name="header">
    <header class="bg-[#eaf0f6] border-b border-[#cdd7e4] py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-[#002d62] text-center break-words">
          {{ __('Registering students for') }}: {{ $competition->name }}
        </h2>
      </div>
    </header>
  </x-slot>

  <div class="py-10 bg-[#f9fbfd]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 bg-white p-6 rounded-xl shadow">

      <form id="students-form" method="POST" action="{{ route('competitions.registerStudents', $competition) }}">
        @csrf

        <h3 class="text-xl font-bold text-[#002d62] mb-4">{{ __("School and contact details") }}</h3>

        <div class="mb-4">
          <label class="block text-sm font-medium">{{ __('School') }}</label>
          <input type="text" name="school" maxlength="255" class="form-input w-full char-field" required>
          <p class="text-xs text-gray-500 mt-1"><span class="count">0</span>/255 {{ __('characters') }}</p>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium">{{ __('School address') }}</label>
          <input type="text" name="school_address" maxlength="255" class="form-input w-full char-field" required>
          <p class="text-xs text-gray-500 mt-1"><span class="count">0</span>/255 {{ __('characters') }}</p>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium">{{ __('Contact (e-mail or phone)') }}</label>
          <input type="text" name="contact" maxlength="255" class="form-input w-full char-field" required>
          <p class="text-xs text-gray-500 mt-1"><span class="count">0</span>/255 {{ __('characters') }}</p>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium">{{ __("Guardian's data") }}</label>

          <div class="flex items-center gap-3 mt-2">
            <input type="checkbox" id="toggle_teacher" class="form-checkbox">
            <label for="toggle_teacher" class="text-sm">{{ __('Add teacher') }}</label>
          </div>
          <div class="hidden mt-2" id="teacher_wrapper">
            <input type="text" name="teacher" id="teacher_input" maxlength="255" class="form-input w-full char-field" disabled>
            <p class="text-xs text-gray-500 mt-1"><span class="count">0</span>/255 {{ __('characters') }}</p>
          </div>

          <div class="flex items-center gap-3 mt-4">
            <input type="checkbox" id="toggle_guardian" class="form-checkbox">
            <label for="toggle_guardian" class="text-sm">{{ __('Add guardian') }}</label>
          </div>
          <div class="hidden mt-2" id="guardian_wrapper">
            <input type="text" name="guardian" id="guardian_input" maxlength="255" class="form-input w-full char-field" disabled>
            <p class="text-xs text-gray-500 mt-1"><span class="count">0</span>/255 {{ __('characters') }}</p>
          </div>
        </div>

        <h3 class="text-xl font-bold text-[#002d62] mt-8 mb-4">{{ __('Student list') }}</h3>

        <div id="students-wrapper">
          <div class="student-item mb-4 border border-[#cdd7e4] p-4 rounded relative bg-[#f9fbfd]">
            <button
              type="button"
              class="remove-student absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full w-7 h-7 items-center justify-center hidden"
              aria-label="{{ __('Remove student') }}"
            >×</button>

            <div class="mb-2">
              <label class="block text-sm font-medium">{{ __('First name') }}</label>
              <input type="text" name="students[0][name]" maxlength="255" class="form-input w-full char-field" required>
              <p class="text-xs text-gray-500 mt-1"><span class="count">0</span>/255 {{ __('characters') }}</p>
            </div>

            <div class="mb-2">
              <label class="block text-sm font-medium">{{ __('Last name') }}</label>
              <input type="text" name="students[0][last_name]" maxlength="255" class="form-input w-full char-field" required>
              <p class="text-xs text-gray-500 mt-1"><span class="count">0</span>/255 {{ __('characters') }}</p>
            </div>

            <div class="mb-2">
              <label class="block text-sm font-medium">{{ __('Class') }}</label>
              <select name="students[0][class]" class="form-select w-full" required>
                <option value="" disabled selected>{{ __('-- select --') }}</option>
                @foreach($classes as $c)
                  <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
              </select>
            </div>

            <div class="mb-2 flex items-center space-x-2">
              <input type="checkbox" name="students[0][statement]" value="1" class="form-checkbox" required>
              <span class="text-sm">{{ __('I agree to the processing of my personal data') }}</span>
            </div>
          </div>
        </div>

        <button type="button" id="add-student" class="bg-[#002d62] text-white px-4 py-2 rounded hover:bg-[#001b3c] mb-6">
          ➕ {{ __('Add another student') }}
        </button>

        <div class="flex justify-between items-center">
          <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
            {{ __('Save students') }}
          </button>

          <a href="{{ route('competitions.show', $competition) }}" class="text-blue-500 hover:underline">
            ← {{ __('Back to competition details') }}
          </a>
        </div>
      </form>
    </div>
  </div>

  <script>
    function setupToggle(checkId, wrapperId, inputId) {
      const check = document.getElementById(checkId);
      const wrapper = document.getElementById(wrapperId);
      const input = document.getElementById(inputId);

      check.addEventListener('change', () => {
        if (check.checked) {
          wrapper.classList.remove('hidden');
          input.disabled = false;
          input.focus();
        } else {
          wrapper.classList.add('hidden');
          input.disabled = true;
          input.value = '';
          updateCounter(input);
        }
      });
    }

    setupToggle('toggle_teacher', 'teacher_wrapper', 'teacher_input');
    setupToggle('toggle_guardian', 'guardian_wrapper', 'guardian_input');

    function updateCounter(el) {
      const wrapper = el.closest('div');
      const counter = wrapper ? wrapper.querySelector('.count') : null;
      if (counter) counter.textContent = el.value.length;
    }

    document.addEventListener('input', (e) => {
      if (e.target.classList.contains('char-field')) {
        updateCounter(e.target);
      }
    });

    document.querySelectorAll('.char-field').forEach(updateCounter);

    const wrapper = document.getElementById('students-wrapper');
    const addBtn = document.getElementById('add-student');
    let idx = 1;

    function resetFields(item) {
      item.querySelectorAll('input, select').forEach(el => {
        if (el.name && el.name.startsWith('students[')) {
          el.name = el.name.replace(/students\[\d+]/, `students[${idx}]`);
        }

        if (el.tagName === 'SELECT') {
          el.selectedIndex = 0;
        } else if (el.type === 'checkbox') {
          el.checked = false;
        } else {
          el.value = '';
          if (el.classList.contains('char-field')) updateCounter(el);
        }
      });
    }

    function enableRemove(item) {
      const btn = item.querySelector('.remove-student');
      btn.classList.remove('hidden');
      btn.addEventListener('click', () => item.remove());
    }

    wrapper.querySelector('.student-item .remove-student').classList.add('hidden');

    addBtn.addEventListener('click', () => {
      const prototype = wrapper.querySelector('.student-item');
      const item = prototype.cloneNode(true);

      resetFields(item);
      wrapper.appendChild(item);
      enableRemove(item);

      idx++;
    });
  </script>
</x-app-layout>
