<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <div class="w-full h-screen flex">

        <!-- LEFT FORM -->
        <div class="w-1/2 bg-[#070a06] flex flex-col justify-center px-24 text-white">
            
            <h1 class="text-4xl font-bold">Hola!</h1>
            <p class="mt-1 text-lg text-green-400">Estás a un paso de unirte.</p>

            <form @submit.prevent="submit" class="mt-8 space-y-5 w-[85%]">

                <!-- NAME -->
                <div>
                    <input
                        type="text"
                        v-model="form.name"
                        placeholder="Full Name"
                        class="w-full px-4 py-3 bg-transparent border border-gray-500 rounded-full text-white placeholder-gray-300 focus:outline-none"
                        required
                    />
                    <div class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                <!-- EMAIL -->
                <div>
                    <input
                        type="email"
                        v-model="form.email"
                        placeholder="Email Address"
                        class="w-full px-4 py-3 bg-transparent border border-gray-500 rounded-full text-white placeholder-gray-300 focus:outline-none"
                        required
                    />
                    <div class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                </div>

                <!-- PASSWORD -->
                <div>
                    <input
                        type="password"
                        v-model="form.password"
                        placeholder="Password"
                        class="w-full px-4 py-3 bg-transparent border border-gray-500 rounded-full text-white placeholder-gray-300 focus:outline-none"
                        required
                    />
                    <div class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
                </div>

                <!-- CONFIRM PASSWORD -->
                <div>
                    <input
                        type="password"
                        v-model="form.password_confirmation"
                        placeholder="Confirm Password"
                        class="w-full px-4 py-3 bg-transparent border border-gray-500 rounded-full text-white placeholder-gray-300 focus:outline-none"
                        required
                    />
                    <div class="text-red-500 text-sm mt-1">{{ form.errors.password_confirmation }}</div>
                </div>

                <!-- BUTTON -->
                <button
                    :disabled="form.processing"
                    class="w-full py-3 rounded-full bg-gradient-to-r from-[#00FF00] to-[#008000] text-white font-semibold hover:opacity-80 transition"
                >
                    Sign Up
                </button>

                <div class="text-sm mt-2 text-gray-300">
                    Already registered?
                    <Link
                        :href="route('login')"
                        class="underline text-green-400 hover:text-green-300"
                    >
                        Login
                    </Link>
                </div>
            </form>
        </div>

        <!-- RIGHT SIDE WITH IMAGE -->
        <div class="w-1/2 relative flex items-center justify-center overflow-hidden">

            <img src="/images/derecha.png" class="absolute inset-0 w-full h-full object-cover" />

            <div class="absolute inset-0 bg-black/40"></div>
        </div>
    </div>
</template>
