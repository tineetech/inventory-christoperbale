@extends('layouts.main')

@section('content')
    <div class="layout-content">
        <div class="container-fluid flex-grow-1 container-p-y">
            <h4 class="font-weight-bold py-3 mb-0">Chatbot FAQ</h4>
            <div class="text-muted small mt-0 mb-4 d-block breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#"><i class="feather icon-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="#">Barang - Web</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('chatbot_faq.index') }}">Chatbot FAQ</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>

            <div class="card col-lg-12 mb-4">
                <h6 class="card-header">Tambah FAQ Chatbot Baru</h6>
                <div class="card-body">
                    <form action="{{ route('chatbot_faq.store') }}" method="POST" id="faqForm">
                        @csrf

                        <div class="form-group">
                            <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                            <input type="text" name="pertanyaan" class="form-control @error('pertanyaan') is-invalid @enderror"
                                placeholder="Contoh: Bagaimana cara melakukan refund?" value="{{ old('pertanyaan') }}" required>
                            @error('pertanyaan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Jawaban <span class="text-danger">*</span></label>
                            <textarea name="jawaban" class="form-control @error('jawaban') is-invalid @enderror" rows="5"
                                placeholder="Jawaban lengkap dari pertanyaan FAQ..." required>{{ old('jawaban') }}</textarea>
                            @error('jawaban')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="form-label">Keyword (pisahkan dengan koma)</label>
                                <input type="text" name="keywords" class="form-control @error('keywords') is-invalid @enderror"
                                    placeholder="Contoh: refund, uang kembali, pengembalian" value="{{ old('keywords') }}">
                                @error('keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Urutan</label>
                                <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror"
                                    placeholder="0" value="{{ old('urutan') }}" min="0">
                                @error('urutan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ old('is_active', '1') == '0' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                                @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="card bg-light mb-3">
                            <div class="card-body py-3 px-3">
                                <div class="form-row align-items-center">
                                    <div class="form-group col-md-6 mb-0">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="quick_question"
                                                name="quick_question" value="1" {{ old('quick_question') ? 'checked' : '' }}>
                                            <label class="custom-control-label form-label mb-0" for="quick_question">
                                                <strong>Quick Question</strong>
                                                <small class="text-muted d-block">Tampilkan sebagai pertanyaan cepat di chatbot</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6 mb-0">
                                        <label class="form-label mb-1">Urutan Quick Question</label>
                                        <input type="number" name="quick_question_order" class="form-control @error('quick_question_order') is-invalid @enderror"
                                            placeholder="0" value="{{ old('quick_question_order') }}" min="0">
                                        @error('quick_question_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('chatbot_faq.index') }}" class="btn btn-secondary"><i class="feather icon-arrow-left"></i> Kembali</a>
                            <button type="submit" class="btn btn-primary"><i class="feather icon-save"></i> Simpan FAQ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection