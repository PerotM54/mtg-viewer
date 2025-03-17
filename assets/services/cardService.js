export async function fetchAllCards(page, code) {
    const uri = `/api/card/all?page=${page}&code=${code}`;
    const response = await fetch(uri);
    if (!response.ok) throw new Error('Failed to fetch cards');
    const result = await response.json();
    return result;
}

export async function fetchCard(uuid) {
    const response = await fetch(`/api/card/${uuid}`);
    if (response.status === 404) return null;
    if (!response.ok) throw new Error('Failed to fetch card');
    const card = await response.json();
    card.text = card.text.replaceAll('\\n', '\n');
    return card;
}

export async function searchCard(name) {
    const uri = `/api/card/search?q=${name}`;
    const response = await fetch(uri);
    if (!response.ok) throw new Error('Failed to fetch cards');
    const result = await response.json();
    return result;
}

export async function loadAllCodes() {
    const uri = '/api/card/codes';
    const response = await fetch(uri);
    if (!response.ok) throw new Error('Failed to fetch cards');
    const result = await response.json();
    return result;
}
