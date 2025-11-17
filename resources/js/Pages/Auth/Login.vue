<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-dark-bg text-white">
        
        <div class="flex w-full max-w-6xl rounded-lg overflow-hidden shadow-2xl shadow-black/50">

            <div class="w-1/2 p-8 flex flex-col justify-center bg-dark-bg relative z-10">
                
                <div class="mb-8 absolute top-8 left-8 flex items-end">
                    <img src="/images/logo.png" alt="Jackpot Celestial" class="h-10">
                </div>

                <div class="mt-20">
                    <h2 class="text-3xl font-bold text-neon-green mb-2">Hello Again!</h2>
                    <p class="text-placeholder-gray mb-8">Welcome Back</p>
                </div>

                <div v-if="status" class="mb-4 text-sm font-medium text-neon-green">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="w-full">
                    
                    <div class="mb-6 relative">
                        <label for="email" class="sr-only">Email Address</label>
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full bg-input-bg text-white border-0 rounded-full py-3 px-6 pl-12 focus:ring-2 focus:ring-neon-green focus:border-neon-green placeholder-placeholder-gray"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Email Address"
                        />
                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-placeholder-gray">📧</span>
                        <InputError class="mt-2 text-red-400" :message="form.errors.email" />
                    </div>

                    <div class="mb-6 relative">
                        <label for="password" class="sr-only">Password</label>
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full bg-input-bg text-white border-0 rounded-full py-3 px-6 pl-12 focus:ring-2 focus:ring-neon-green focus:border-neon-green placeholder-placeholder-gray"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="Password"
                        />
                        <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-placeholder-gray">🔒</span>
                        <InputError class="mt-2 text-red-400" :message="form.errors.password" />
                    </div>

                    <div class="flex flex-col items-center justify-center mt-8">
                        <PrimaryButton
                            class="w-full py-3 px-6 rounded-full text-lg font-semibold bg-gradient-to-r from-neon-green to-darker-green hover:from-darker-green hover:to-neon-green text-black shadow-lg shadow-neon-green/30"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Iniciar sesion
                        </PrimaryButton>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="mt-6 text-sm text-link-gray hover:text-placeholder-gray"
                        >
                            ¿Has olvidado tu contraseña?

                        </Link>
                    </div>
                </form>
            </div>

            <div class="w-1/2 bg-section-green-dark relative flex items-center justify-center overflow-hidden">
                
                <div class="absolute inset-0" style="background-image: url('/images/derecha.png'); background-size: cover; background-position: center; opacity: 0.8; mix-blend-mode: screen;">
                    <div class="absolute inset-0 bg-black opacity-40"></div>
                </div>

            </div>
        </div>
    </div>
</template>