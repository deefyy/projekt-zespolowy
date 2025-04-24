<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      Rejestracja uczniów na: {{ $competition->name }}
    </h2>
  </x-slot>

  <div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow rounded">
      <form method="POST" action="{{ route('competitions.registerStudents', $competition) }}">
        @csrf

        <div id="students-wrapper">
          <div class="student-item mb-4 border p-4 rounded">
            {{-- Imię --}}
            <div class="mb-2">
              <label class="block text-sm font-medium">Imię</label>
              <input type="text" name="students[0][name]" class="form-input w-full" required>
            </div>
            {{-- Nazwisko --}}
            <div class="mb-2">
              <label class="block text-sm font-medium">Nazwisko</label>
              <input type="text" name="students[0][last_name]" class="form-input w-full" required>
            </div>
            {{-- Klasa --}}
            <div class="mb-2">
              <label class="block text-sm font-medium">Klasa</label>
              <input type="text" name="students[0][class]" class="form-input w-full" required>
            </div>
            {{-- Szkoła --}}
            <div class="mb-2">
              <label class="block text-sm font-medium">Szkoła</label>
              <input type="text" name="students[0][school]" class="form-input w-full" required>
            </div>
          </div>
        </div>

        <button type="button" id="add-student"
                class="bg-blue-500 text-white px-3 py-1 rounded mb-4">
          Dodaj kolejnego ucznia
        </button>

        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">
          Zapisz uczniów
        </button>
      </form>

      <a href="{{ route('competitions.show', $competition) }}"
         class="text-blue-500 hover:underline mt-4 block">
        ← Wróć do szczegółów konkursu
      </a>
    </div>
  </div>

  <script>
    let idx = 1;
    document.getElementById('add-student').addEventListener('click', () => {
      const wrapper = document.getElementById('students-wrapper');
      const item = document.querySelector('.student-item').cloneNode(true);
      item.querySelectorAll('input').forEach(input => {
        const name = input.getAttribute('name').replace(/\d+/, idx);
        input.setAttribute('name', name);
        input.value = '';
      });
      wrapper.appendChild(item);
      idx++;
    });
  </script>
</x-app-layout>
