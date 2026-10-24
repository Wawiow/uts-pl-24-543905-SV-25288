<script setup>
import { ref, onMounted } from 'vue';
import api from './services/api';

const email = ref('');
const password = ref('');
const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const user = ref(JSON.parse(localStorage.getItem('user') || 'null'));

const motors = ref([]);
const loadingMotors = ref(false);
const editingId = ref(null);

const form = ref({
    name: '',
    brand: '',
    plate_number: '',
    price_per_day: '',
    status: 'available',
});

async function login() {
    loading.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        const response = await api.post('/login', {
            email: email.value,
            password: password.value,
        });

        localStorage.setItem('token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.user));

        user.value = response.data.user;
        password.value = '';
        successMessage.value = 'Login berhasil!';
        await loadMotors();
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message ||
            'Tidak dapat terhubung ke server Laravel.';
    } finally {
        loading.value = false;
    }
}

async function loadMotors() {
    loadingMotors.value = true;
    errorMessage.value = '';

    try {
        const response = await api.get('/motors');
        motors.value = response.data.data ?? response.data;
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message ||
            'Gagal mengambil data motor.';
    } finally {
        loadingMotors.value = false;
    }
}

function resetForm() {
    editingId.value = null;
    form.value = {
        name: '',
        brand: '',
        plate_number: '',
        price_per_day: '',
        status: 'available',
    };
}

function editMotor(motor) {
    editingId.value = motor.id;
    form.value = {
        name: motor.name,
        brand: motor.brand,
        plate_number: motor.plate_number,
        price_per_day: motor.price_per_day,
        status: motor.status,
    };
    errorMessage.value = '';
    successMessage.value = '';
}

async function saveMotor() {
    errorMessage.value = '';
    successMessage.value = '';

    const payload = {
        ...form.value,
        price_per_day: Number(form.value.price_per_day),
    };

    try {
        if (editingId.value !== null) {
            await api.put(`/motors/${editingId.value}`, payload);
            successMessage.value = 'Data motor berhasil diperbarui.';
        } else {
            await api.post('/motors', payload);
            successMessage.value = 'Motor berhasil ditambahkan.';
        }

        resetForm();
        await loadMotors();
    } catch (error) {
        if (error.response?.status === 422) {
            const errors = error.response.data.errors || {};
            errorMessage.value = Object.values(errors).flat().join(' ');
        } else {
            errorMessage.value =
                error.response?.data?.message ||
                'Gagal menyimpan data motor.';
        }
    }
}

async function deleteMotor(motor) {
    if (!confirm(`Yakin ingin menghapus motor ${motor.name}?`)) {
        return;
    }

    errorMessage.value = '';
    successMessage.value = '';

    try {
        await api.delete(`/motors/${motor.id}`);
        successMessage.value = 'Data motor berhasil dihapus.';
        await loadMotors();
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message ||
            'Gagal menghapus data motor.';
    }
}

async function logout() {
    try {
        await api.post('/logout');
    } catch (error) {
        // Hapus sesi lokal jika request logout gagal.
    }

    localStorage.removeItem('token');
    localStorage.removeItem('user');

    user.value = null;
    motors.value = [];
    resetForm();
    email.value = '';
    password.value = '';
    successMessage.value = '';
    errorMessage.value = '';
}

onMounted(() => {
    if (user.value && localStorage.getItem('token')) {
        loadMotors();
    } else {
        user.value = null;
    }
});
</script>

<template>
    <main class="container">
        <section v-if="!user" class="login-card">
            <h1>Rental Motor</h1>
            <p class="subtitle">Silakan login untuk melanjutkan</p>

            <form @submit.prevent="login">
                <label for="email">Email</label>
                <input
                    id="email"
                    v-model="email"
                    type="email"
                    autocomplete="username"
                    required
                />

                <label for="password">Password</label>
                <input
                    id="password"
                    v-model="password"
                    type="password"
                    autocomplete="current-password"
                    required
                />

                <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

                <button type="submit" :disabled="loading">
                    {{ loading ? 'Memproses...' : 'Login' }}
                </button>
            </form>
        </section>

        <section v-else class="dashboard">
            <header class="topbar">
                <div>
                    <h1>Rental Motor</h1>
                    <p>Selamat datang, {{ user.name }}!</p>
                </div>
                <button class="logout-button" @click="logout">Logout</button>
            </header>

            <p v-if="errorMessage" class="error message">{{ errorMessage }}</p>
            <p v-if="successMessage" class="success message">
                {{ successMessage }}
            </p>

            <div class="content">
                <section class="card">
                    <h2>{{ editingId ? 'Edit Motor' : 'Tambah Motor' }}</h2>

                    <form @submit.prevent="saveMotor">
                        <label for="name">Nama Motor</label>
                        <input id="name" v-model="form.name" required />

                        <label for="brand">Merek</label>
                        <input id="brand" v-model="form.brand" required />

                        <label for="plate">Nomor Plat</label>
                        <input
                            id="plate"
                            v-model="form.plate_number"
                            placeholder="Contoh: AD 1234 XYZ"
                            required
                        />

                        <label for="price">Harga Sewa per Hari (Rp)</label>
                        <input
                            id="price"
                            v-model="form.price_per_day"
                            type="number"
                            min="1"
                            required
                        />

                        <label for="status">Status</label>
                        <select id="status" v-model="form.status" required>
                            <option value="available">Tersedia</option>
                            <option value="rented">Disewa</option>
                        </select>

                        <button type="submit">
                            {{ editingId ? 'Simpan Perubahan' : 'Tambah Motor' }}
                        </button>

                        <button
                            v-if="editingId"
                            type="button"
                            class="secondary-button"
                            @click="resetForm"
                        >
                            Batal Edit
                        </button>
                    </form>
                </section>

                <section class="card motor-list">
                    <h2>Daftar Motor</h2>

                    <button
                        class="secondary-button refresh-button"
                        @click="loadMotors"
                        :disabled="loadingMotors"
                    >
                        {{ loadingMotors ? 'Memuat...' : 'Muat Ulang' }}
                    </button>

                    <p v-if="loadingMotors">Memuat data motor...</p>
                    <p v-else-if="motors.length === 0">
                        Belum ada data motor.
                    </p>

                    <div v-else class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Motor</th>
                                    <th>Plat</th>
                                    <th>Harga/Hari</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="motor in motors" :key="motor.id">
                                    <td>
                                        <strong>{{ motor.name }}</strong>
                                        <br />
                                        <span class="muted">{{ motor.brand }}</span>
                                    </td>
                                    <td>{{ motor.plate_number }}</td>
                                    <td>
                                        {{
                                            Number(motor.price_per_day)
                                                .toLocaleString('id-ID', {
                                                    style: 'currency',
                                                    currency: 'IDR',
                                                    maximumFractionDigits: 0
                                                })
                                        }}
                                    </td>
                                    <td>
                                        {{ motor.status === 'available' ? 'Tersedia' : 'Disewa' }}
                                    </td>
                                    <td class="actions">
                                        <button
                                            class="edit-button"
                                            @click="editMotor(motor)"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            class="delete-button"
                                            @click="deleteMotor(motor)"
                                        >
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </section>
    </main>
</template>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    color: #1f2937;
    background: #f3f4f6;
}

button,
input,
select {
    font: inherit;
}

.container {
    min-height: 100vh;
    padding: 28px;
}

.login-card {
    width: 100%;
    max-width: 400px;
    margin: 10vh auto;
    padding: 32px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 16px #00000012;
}

h1,
h2 {
    margin-top: 0;
}

.login-card h1 {
    text-align: center;
}

.subtitle {
    margin-bottom: 24px;
    color: #6b7280;
    text-align: center;
}

label {
    display: block;
    margin: 16px 0 8px;
    font-weight: bold;
}

input,
select {
    width: 100%;
    padding: 11px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: white;
}

button {
    padding: 10px 14px;
    border: none;
    border-radius: 6px;
    background: #2563eb;
    color: white;
    cursor: pointer;
}

form > button {
    width: 100%;
    margin-top: 20px;
}

button:disabled {
    opacity: 0.6;
    cursor: wait;
}

.dashboard {
    max-width: 1400px;
    margin: 0 auto;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    padding: 24px;
    background: white;
    border-radius: 12px;
}

.topbar h1 {
    margin-bottom: 8px;
}

.topbar p {
    margin: 0;
    color: #6b7280;
}

.logout-button {
    background: #374151;
}

.content {
    display: grid;
    grid-template-columns: minmax(280px, 340px) minmax(0, 1fr);
    gap: 24px;
    align-items: start;
}

.card {
    min-width: 0;
    padding: 24px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 16px #00000008;
}

.card h2 {
    font-size: 20px;
}

.secondary-button {
    background: #6b7280;
}

.refresh-button {
    margin-bottom: 16px;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

th,
td {
    padding: 12px 10px;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}

th {
    background: #f9fafb;
}

.muted {
    color: #6b7280;
    font-size: 13px;
}

.actions {
    display: flex;
    gap: 8px;
}

.edit-button {
    background: #d97706;
}

.delete-button {
    background: #dc2626;
}

.error {
    color: #dc2626;
}

.success {
    color: #15803d;
}

.message {
    margin: 12px 0;
}

@media (max-width: 900px) {
    .content {
        grid-template-columns: 1fr;
    }

    .container {
        padding: 16px;
    }

    .topbar {
        padding: 18px;
    }
}
</style>
