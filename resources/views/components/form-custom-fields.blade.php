@props(['questions' => null, 'values' => [], 'title' => 'Pertanyaan Formulir'])

@if(!empty($questions) && count($questions) > 0)
    <div class="pt-4 border-t border-slate-200 space-y-4">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wider text-sky-800 bg-sky-50 px-2.5 py-1 rounded-md border border-sky-200">
                {{ $title }}
            </span>
        </div>

        @foreach($questions as $cq)
            @php
                $val = $values[$cq->kode] ?? null;
            @endphp
            <div>
                <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1.5">
                    {{ $cq->label }}
                    @if($cq->is_wajib)
                        <span class="text-rose-600 font-black" title="Wajib diisi">*</span>
                    @else
                        <span class="text-xs font-normal text-slate-500">(Opsional)</span>
                    @endif
                </label>

                @if($cq->bantuan)
                    <p class="text-xs text-slate-500 mb-1.5">{{ $cq->bantuan }}</p>
                @endif

                @if($cq->tipe === 'text')
                    <input type="text" name="custom_fields[{{ $cq->kode }}]"
                           value="{{ old('custom_fields.'.$cq->kode, $val) }}"
                           placeholder="{{ $cq->placeholder ?: 'Tuliskan jawaban Anda...' }}"
                           {{ $cq->is_wajib ? 'required' : '' }}
                           class="w-full px-4 py-3 sm:py-3.5 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                @elseif($cq->tipe === 'number')
                    <input type="number" name="custom_fields[{{ $cq->kode }}]"
                           value="{{ old('custom_fields.'.$cq->kode, $val) }}"
                           placeholder="{{ $cq->placeholder ?: 'Masukkan angka...' }}"
                           {{ $cq->is_wajib ? 'required' : '' }}
                           class="w-full px-4 py-3 sm:py-3.5 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                @elseif($cq->tipe === 'textarea')
                    <textarea name="custom_fields[{{ $cq->kode }}]" rows="3"
                              placeholder="{{ $cq->placeholder ?: 'Tuliskan jawaban Anda...' }}"
                              {{ $cq->is_wajib ? 'required' : '' }}
                              class="w-full px-4 py-3 sm:py-3.5 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white resize-none transition">{{ old('custom_fields.'.$cq->kode, $val) }}</textarea>
                @elseif($cq->tipe === 'select')
                    <select name="custom_fields[{{ $cq->kode }}]" {{ $cq->is_wajib ? 'required' : '' }}
                            class="w-full px-4 py-3 sm:py-3.5 text-base font-medium text-slate-900 border-2 border-slate-300 hover:border-slate-400 rounded-2xl focus:ring-4 focus:ring-sky-500/20 focus:border-sky-600 outline-none bg-white transition min-h-[50px]">
                        <option value="">-- Pilih Jawaban --</option>
                        @foreach($cq->opsi ?? [] as $opt)
                            <option value="{{ $opt }}" @selected(old('custom_fields.'.$cq->kode, $val) === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                @elseif($cq->tipe === 'checkbox')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                        @foreach($cq->opsi ?? [] as $opt)
                            @php
                                $checkedList = (array) old('custom_fields.'.$cq->kode, (is_array($val) ? $val : []));
                            @endphp
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border-2 border-slate-200 hover:bg-slate-50 cursor-pointer bg-white">
                                <input type="checkbox" name="custom_fields[{{ $cq->kode }}][]" value="{{ $opt }}"
                                       @checked(in_array($opt, $checkedList))
                                       class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500">
                                <span class="text-sm font-semibold text-slate-800">{{ $opt }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
