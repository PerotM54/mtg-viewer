<script setup>
import { onMounted, ref } from 'vue';
import { fetchAllCards, loadAllCodes } from '../services/cardService';

const cards = ref([]);
const codes = ref([]);
const loadingCards = ref(true);
const loadingCodes = ref(true);

const page = ref(1);

const currentCode = ref('');

async function loadCards() {
    loadingCards.value = true;
    cards.value = await fetchAllCards(page.value, currentCode.value);
    loadingCards.value = false;
}

async function loadCodes() {
    loadingCodes.value = true;
    codes.value = await loadAllCodes(page.value);
    loadingCodes.value = false;
}

onMounted(() => {
    loadCards();
    loadCodes();
});

function setPage(v) {
    if (page.value + v < 1) return;
    page.value += v;
    loadCards();
}

</script>

<template>
    <div>
        <h1>Toutes les cartes</h1>
    </div>
    <div>
        <div v-if="!loadingCodes">
            <label for="codes">Select a code:</label>
            <select @change="loadCards" id="codes" v-model="currentCode">
                <option value="">Tout</option>
                <option v-for="code in codes" :key="code">
                    {{ code }}
                </option>
            </select>
        </div>

        <button type="button" @click="setPage(-1)">Page précédente</button>
        <p>Page {{ page }}</p>
        <button type="button" @click="setPage(1)">Page suivante</button>
    </div>
    <div class="card-list">
        <div v-if="loadingCards">Loading...</div>
        <div v-else>
            <div class="card-result" v-for="card in cards" :key="card.id">
                <router-link :to="{ name: 'get-card', params: { uuid: card.uuid } }">
                    {{ card.name }} <span>({{ card.uuid }})</span>
                </router-link>
            </div>
        </div>
    </div>
</template>
