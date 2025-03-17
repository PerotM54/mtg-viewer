<script setup>
import { onMounted, ref } from 'vue';
import { searchCard } from '../services/cardService';

const cards = ref([]);
const loadingCards = ref(true);

const search = ref('');

async function loadCards() {
    loadingCards.value = true;
    cards.value = await searchCard(search.value);
    loadingCards.value = false;
}

onMounted(() => {
    loadCards();
});
</script>

<template>
    <div>
        <h1>Rechercher une Carte</h1>
        <input @keyup="loadCards" placeholder="Nom de la carte" minlength="3" v-model="search">
    </div>
    <div class="card-list">
        <div v-if="loadingCards">Loading...</div>
        <div v-else>
            <div class="card" v-for="card in cards" :key="card.id">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }"> {{ card.name }} - {{ card.uuid }} </router-link>
            </div>
        </div>
    </div>
</template>
