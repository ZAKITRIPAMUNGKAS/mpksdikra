const PRESENTATION_STATUSES = [
    'Mencari data kader…',
    'Membaca dokumen…',
    'Menyusun jawaban…',
];

const LOW_CONFIDENCE_SCORE = 0.55;

function normalizeSource(source, index) {
    const score = Number(source?.score ?? 0.75);

    return {
        id: String(source?.id ?? index + 1),
        title: source?.title || 'Sumber kegiatan',
        page: source?.page ?? null,
        snippet: source?.snippet || '',
        url: source?.url ?? null,
        score: Number.isFinite(score) ? score : 0,
        badge: source?.badge || null,
    };
}

function onlyKnownCitations(content, sources) {
    const sourceIds = new Set(sources.map((source) => source.id));

    return String(content || '').replace(/\[(\d+)\]/g, (citation, id) => (
        sourceIds.has(id) ? citation : ''
    ));
}

export function normalizeRagResponse(response, context = {}) {
    const isChat = response?.intent === 'chat' || response?.is_chat === true;
    const sources = Array.isArray(response?.sources)
        ? response.sources.map(normalizeSource)
        : [];
    const grounded = isChat || (sources.length > 0 && sources.some((source) => source.score >= LOW_CONFIDENCE_SCORE));

    return {
        id: `msg-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
        role: 'assistant',
        content: onlyKnownCitations(response?.message || response?.reply || '', sources),
        status: 'complete',
        sources,
        actions: Array.isArray(response?.action_buttons) ? response.action_buttons : (Array.isArray(response?.actions) ? response.actions : []),
        action_buttons: Array.isArray(response?.action_buttons) ? response.action_buttons : (Array.isArray(response?.actions) ? response.actions : []),
        component: null,
        createdAt: new Date().toISOString(),
        context,
        confidence: isChat ? 'grounded' : (grounded ? 'grounded' : 'low'),
        notice: isChat ? null : (grounded ? null : (response?.notice || 'Saya kurang yakin dengan informasi ini karena berada di luar basis data resmi MPKSDI.')),
        buildLog: [
            { text: 'Membaca dokumen dan kurikulum perkaderan', status: 'done' },
            { text: 'Mencocokkan dengan database kader aktif', status: 'done' },
            { text: 'Menyusun rekomendasi berbasis data riil', status: 'done' },
        ],
        logCompleted: true,
        logExpanded: false,
    };
}

export function createPresentationEvents(message, { reducedMotion = false } = {}) {
    const statusEvents = PRESENTATION_STATUSES.map((payload) => ({ type: 'status', payload }));
    const tokenPayload = reducedMotion ? message.content : String(message.content || '');

    return [
        ...statusEvents,
        ...(message.sources?.length ? [{ type: 'sources', payload: message.sources }] : []),
        { type: 'token', payload: tokenPayload },
        ...(message.component ? [{ type: 'component', payload: message.component }] : []),
        { type: 'done', payload: message },
    ];
}

export { PRESENTATION_STATUSES };
