<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { dashboard, login } from '@/routes';
</script>

<template>
    <Head title="Airbnb Tool" />
    <div class="flex min-h-screen flex-col items-center justify-center bg-background p-6">
        <div class="flex flex-col items-center gap-6">
            <span class="text-6xl">🏡</span>
            <div class="text-center">
                <h1 class="text-2xl font-bold text-foreground">Airbnb Tool</h1>
                <p class="mt-1 text-sm text-muted-foreground">Gestión de propiedades</p>
            </div>
            <Button v-if="$page.props.auth.user" as-child size="lg">
                <Link :href="dashboard()">Ir al Dashboard</Link>
            </Button>
            <Button v-else as-child size="lg">
                <Link :href="login()">Iniciar sesión</Link>
            </Button>
        </div>
    </div>
</template>
