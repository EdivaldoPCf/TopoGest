<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Perfil • Getec Topografia</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
</head>

<body class="min-h-screen bg-[#0B1727] antialiased relative overflow-x-hidden">

    <!-- Background -->
    <div class="fixed inset-0 z-0">
        <img 
            src="{{ asset('images/background-topo.jpg') }}" 
            alt="Background"
            class="w-full h-full object-cover"
        >
        <div class="absolute inset-0 bg-[#001426]/80 backdrop-blur-[2px]"></div>
    </div>

    <!-- Container -->
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 py-10">

        <div class="w-full max-w-4xl">

            <!-- Header -->
            <div class="flex flex-col items-center mb-10">
                <div class="bg-[#003366]/90 backdrop-blur-xl px-8 py-3 rounded-2xl border border-white/10 shadow-2xl">
                    <h1 class="text-2xl md:text-3xl font-black italic tracking-tight text-white uppercase">
                        Alterar Perfil
                    </h1>
                </div>

                <p class="mt-4 text-sm text-white/70 text-center max-w-lg">
                    Atualize suas informações pessoais, foto de perfil e credenciais de acesso.
                </p>
            </div>

            <!-- Card -->
            <div class="bg-white/10 backdrop-blur-2xl border border-white/10 rounded-[32px] shadow-2xl overflow-hidden">

                <form 
                    id="profile-form"
                    method="POST"
                    action="{{ route('profile.update') }}"
                    enctype="multipart/form-data"
                    class="p-6 md:p-10"
                >
                    @csrf
                    @method('PATCH')

                    <!-- Photo Section -->
                    <div class="flex flex-col items-center mb-12">

                        <label class="group relative cursor-pointer">

                            <div class="relative w-36 h-36 rounded-full overflow-hidden border-4 border-white/20 shadow-2xl">

                                <img
                                    id="current-photo"
                                    src="{{ auth()->user()->photo 
                                        ? asset('storage/' . auth()->user()->photo) 
                                        : asset('images/default-avatar.png') }}"
                                    alt="Foto de perfil"
                                    class="w-full h-full object-cover transition duration-300 group-hover:scale-105"
                                >

                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                    <span class="text-white text-sm font-bold uppercase tracking-wide">
                                        Alterar
                                    </span>
                                </div>
                            </div>

                            <input 
                                type="file"
                                id="photo-input"
                                class="hidden"
                                accept="image/*"
                            >

                            <input 
                                type="hidden"
                                name="cropped_image"
                                id="cropped_image"
                            >
                        </label>

                        <div class="mt-5 text-center">
                            <h2 class="text-white text-xl font-bold uppercase tracking-wide">
                                {{ auth()->user()->name }}
                            </h2>

                            <p class="text-white/60 text-sm mt-1">
                                Clique na imagem para alterar sua foto
                            </p>
                        </div>
                    </div>

                    <!-- Inputs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Nome -->
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-bold uppercase tracking-wide text-white/80">
                                Nome Completo
                            </label>

                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', auth()->user()->name) }}"
                                class="w-full h-14 px-5 rounded-2xl bg-white/10 border border-white/10 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#00A3FF] transition"
                                placeholder="Digite seu nome"
                            >
                        </div>

                        <!-- CPF -->
                        <div>
                            <label class="block mb-2 text-sm font-bold uppercase tracking-wide text-white/80">
                                CPF / CNPJ
                            </label>

                            <input
                                type="text"
                                name="cpf"
                                value="{{ old('cpf', auth()->user()->formatted_cpf) }}"
                                readonly
                                class="w-full h-14 px-5 rounded-2xl bg-white/5 border border-white/10 text-white/50 placeholder-white/40 focus:outline-none cursor-not-allowed transition"
                                placeholder="000.000.000-00"
                            >
                            <p class="mt-2 text-xs text-white/50 leading-relaxed">
                                O CPF/CNPJ não pode ser alterado diretamente. Entre em contato com um administrador se precisar corrigi-lo.
                            </p>
                        </div>

                        <!-- Celular -->
                        <div>
                            <label class="block mb-2 text-sm font-bold uppercase tracking-wide text-white/80">
                                Celular
                            </label>

                            <input
                                type="text"
                                name="phone"
                                value="{{ old('phone', auth()->user()->phone) }}"
                                class="w-full h-14 px-5 rounded-2xl bg-white/10 border border-white/10 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#00A3FF] transition"
                                placeholder="(00) 00000-0000"
                            >
                        </div>

                        <!-- Email -->
                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-bold uppercase tracking-wide text-white/80">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                value="{{ old('email', auth()->user()->email) }}"
                                class="w-full h-14 px-5 rounded-2xl bg-white/10 border border-white/10 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#00A3FF] transition"
                                placeholder="email@exemplo.com"
                            >
                        </div>

                        <!-- Senha -->
                        <div>
                            <label class="block mb-2 text-sm font-bold uppercase tracking-wide text-white/80">
                                Nova Senha
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="w-full h-14 px-5 rounded-2xl bg-white/10 border border-white/10 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#00A3FF] transition"
                                placeholder="••••••••"
                            >
                        </div>

                        <!-- Confirmar Senha -->
                        <div>
                            <label class="block mb-2 text-sm font-bold uppercase tracking-wide text-white/80">
                                Confirmar Senha
                            </label>

                            <input
                                type="password"
                                name="password_confirmation"
                                class="w-full h-14 px-5 rounded-2xl bg-white/10 border border-white/10 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-[#00A3FF] transition"
                                placeholder="••••••••"
                            >
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-5 mt-12">

                        <button
                            type="submit"
                            class="w-full sm:w-auto px-10 h-14 rounded-2xl bg-gradient-to-r from-[#00E676] to-[#00C853] text-black font-black uppercase tracking-wide shadow-2xl hover:scale-105 transition"
                        >
                            Salvar Alterações
                        </button>

                        <a
                            href="{{ route('dashboard') }}"
                            class="w-full sm:w-auto px-10 h-14 rounded-2xl bg-gradient-to-r from-[#FF3D3D] to-[#D50000] text-white font-black uppercase tracking-wide shadow-2xl hover:scale-105 transition flex items-center justify-center"
                        >
                            Voltar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cropper Modal -->
    <div 
        id="cropper-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm p-4"
    >
        <div class="w-full max-w-2xl bg-[#0F2238] border border-white/10 rounded-3xl shadow-2xl overflow-hidden">

            <div class="px-6 py-5 border-b border-white/10">
                <h3 class="text-white text-xl font-bold uppercase tracking-wide">
                    Ajustar Foto de Perfil
                </h3>

                <p class="text-white/60 text-sm mt-1">
                    Ajuste o enquadramento antes de salvar.
                </p>
            </div>

            <div class="p-6">
                <div class="bg-black/30 rounded-2xl overflow-hidden max-h-[500px]">
                    <img id="cropper-image" src="" class="max-w-full">
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-4 mt-6">

                    <button
                        type="button"
                        id="cancel-crop"
                        class="h-12 px-6 rounded-xl bg-white/10 text-white font-bold uppercase tracking-wide hover:bg-white/20 transition"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        id="save-crop"
                        class="h-12 px-6 rounded-xl bg-[#004A7C] text-white font-bold uppercase tracking-wide hover:bg-[#005A99] transition"
                    >
                        Recortar e Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cropper;

        const photoInput = document.getElementById('photo-input');
        const cropperModal = document.getElementById('cropper-modal');
        const cropperImage = document.getElementById('cropper-image');
        const croppedImageInput = document.getElementById('cropped_image');
        const currentPhoto = document.getElementById('current-photo');

        photoInput.addEventListener('change', function (e) {

            const files = e.target.files;

            if (files && files.length > 0) {

                const reader = new FileReader();

                reader.onload = function (event) {

                    cropperImage.src = event.target.result;

                    cropperModal.classList.remove('hidden');
                    cropperModal.classList.add('flex');

                    if (cropper) {
                        cropper.destroy();
                    }

                    cropper = new Cropper(cropperImage, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        background: false,
                        autoCropArea: 1,
                    });
                };

                reader.readAsDataURL(files[0]);
            }
        });

        document.getElementById('save-crop').addEventListener('click', function () {

            const canvas = cropper.getCroppedCanvas({
                width: 500,
                height: 500,
                imageSmoothingQuality: 'high'
            });

            const base64Image = canvas.toDataURL('image/png');

            croppedImageInput.value = base64Image;
            currentPhoto.src = base64Image;

            cropperModal.classList.add('hidden');
            cropperModal.classList.remove('flex');
        });

        document.getElementById('cancel-crop').addEventListener('click', function () {

            cropperModal.classList.add('hidden');
            cropperModal.classList.remove('flex');

            photoInput.value = '';

            if (cropper) {
                cropper.destroy();
            }
        });
    </script>

</body>
</html>