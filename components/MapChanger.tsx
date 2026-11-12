import React, { useCallback, useEffect, useMemo, useState } from 'react';
import tw from 'twin.macro';
import { ServerContext } from '@/state/server';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import Spinner from '@/components/elements/Spinner';
import { Button } from '@/components/elements/button/index';
import Input from '@/components/elements/Input';
import http, { httpErrorToHuman } from '@/api/http';
import FlashMessageRender from '@/components/FlashMessageRender';
import useFlash from '@/plugins/useFlash';

type MapItem = {
    id: string;
    name: string;
    group: string;
    image: string;
    current?: boolean;
};

type Catalog = {
    current?: string | null;
    count: number;
    items: MapItem[];
};

const GROUPS = ['Defuse', 'Hostage', 'Arms Race'];

export default () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { clearFlashes, addFlash, clearAndAddHttpError } = useFlash();
    const [query, setQuery] = useState('');
    const [group, setGroup] = useState('');
    const [loading, setLoading] = useState(true);
    const [busy, setBusy] = useState<string | null>(null);
    const [data, setData] = useState<Catalog | null>(null);

    const load = useCallback(() => {
        setLoading(true);
        http
            .get<Catalog>(`/api/client/extensions/prmmapchanger/servers/${uuid}/maps`)
            .then(({ data: payload }) => setData(payload))
            .catch((error) => clearAndAddHttpError({ key: 'prm-maps', error }))
            .finally(() => setLoading(false));
    }, [uuid, clearAndAddHttpError]);

    useEffect(() => {
        load();
    }, [load]);

    const change = (event: React.MouseEvent, map: MapItem) => {
        event.preventDefault();
        event.stopPropagation();
        if (map.current || busy) return;
        setBusy(map.id);
        clearFlashes('prm-maps');
        http
            .post(`/api/client/extensions/prmmapchanger/servers/${uuid}/change`, { map: map.id }, { timeout: 30000 })
            .then(({ data: payload }) => {
                addFlash({
                    key: 'prm-maps',
                    type: payload?.offline ? 'warning' : 'success',
                    message: payload?.message || `${map.name} seçildi.`,
                });
                setData((prev) =>
                    prev
                        ? {
                              ...prev,
                              current: map.id,
                              items: prev.items.map((item) => ({ ...item, current: item.id === map.id })),
                          }
                        : prev
                );
            })
            .catch((error) => {
                addFlash({
                    key: 'prm-maps',
                    type: 'error',
                    message: httpErrorToHuman(error),
                });
            })
            .finally(() => setBusy(null));
    };

    const items = useMemo(() => {
        const q = query.trim().toLowerCase();
        return (data?.items || []).filter((m) => {
            if (group && m.group !== group) return false;
            if (!q) return true;
            return m.name.toLowerCase().includes(q) || m.id.toLowerCase().includes(q);
        });
    }, [data, query, group]);

    const counts = useMemo(() => {
        const out: Record<string, number> = {};
        (data?.items || []).forEach((m) => {
            out[m.group] = (out[m.group] || 0) + 1;
        });
        return out;
    }, [data]);

    const imgSrc = (map: MapItem) =>
        `/api/client/extensions/prmmapchanger/servers/${uuid}/maps/${encodeURIComponent(map.id)}/image`;

    return (
        <ServerContentBlock title={'Map Changer'}>
            <FlashMessageRender byKey={'prm-maps'} css={tw`mb-4`} />

            <div css={tw`flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4`}>
                <div>
                    <h2 css={tw`text-xl text-neutral-100 font-semibold`}>PRM Map Changer</h2>
                    <p css={tw`text-sm text-neutral-400 mt-1`}>
                        Tek tıkla harita değiştir. Sunucu açıksa hemen changelevel; kapalıysa sonraki açılışta yüklenir.
                    </p>
                    {data?.current ? (
                        <p css={tw`text-sm text-green-400 mt-2`}>Aktif: {data.current}</p>
                    ) : null}
                </div>
                <Input
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    placeholder={'Ara: mirage, dust2, office…'}
                    css={tw`md:w-72`}
                />
            </div>

            <div css={tw`flex flex-wrap gap-2 mb-5`}>
                <button
                    type={'button'}
                    onClick={() => setGroup('')}
                    css={[
                        tw`text-xs px-3 py-1.5 rounded-md border`,
                        group === ''
                            ? tw`bg-neutral-100 text-neutral-900 border-neutral-100`
                            : tw`bg-neutral-700 text-neutral-300 border-neutral-600`,
                    ]}
                >
                    Tümü ({data?.count || 0})
                </button>
                {GROUPS.map((g) => (
                    <button
                        key={g}
                        type={'button'}
                        onClick={() => setGroup(g)}
                        css={[
                            tw`text-xs px-3 py-1.5 rounded-md border`,
                            group === g
                                ? tw`bg-neutral-100 text-neutral-900 border-neutral-100`
                                : tw`bg-neutral-700 text-neutral-300 border-neutral-600`,
                        ]}
                    >
                        {g} ({counts[g] || 0})
                    </button>
                ))}
            </div>

            {loading && !data ? (
                <Spinner centered size={'large'} />
            ) : (
                <div css={tw`grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4`} style={{ opacity: loading ? 0.6 : 1 }}>
                    {items.map((map) => (
                        <div
                            key={map.id}
                            css={[
                                tw`bg-neutral-700 rounded-lg overflow-hidden border flex flex-col`,
                                map.current ? tw`border-green-500` : tw`border-neutral-600`,
                            ]}
                        >
                            <div css={tw`relative bg-neutral-800`} style={{ paddingTop: '56%' }}>
                                <img
                                    src={imgSrc(map)}
                                    alt={map.name}
                                    css={tw`absolute inset-0 w-full h-full object-cover`}
                                />
                            </div>
                            <div css={tw`p-3 flex flex-col flex-1`}>
                                <div css={tw`flex items-start justify-between gap-2 mb-1`}>
                                    <h3 css={tw`text-sm font-semibold text-neutral-100`}>{map.name}</h3>
                                    {map.current ? (
                                        <span css={tw`text-[10px] uppercase tracking-wide px-2 py-0.5 rounded bg-green-600/30 text-green-300`}>
                                            Aktif
                                        </span>
                                    ) : null}
                                </div>
                                <p css={tw`text-xs text-neutral-400 mb-3`}>
                                    {map.id} · {map.group}
                                </p>
                                <Button
                                    type={'button'}
                                    css={tw`mt-auto w-full`}
                                    disabled={!!busy || map.current}
                                    onClick={(e) => change(e, map)}
                                >
                                    {busy === map.id ? 'Değiştiriliyor…' : map.current ? 'Seçili' : 'Haritayı değiştir'}
                                </Button>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {!loading && items.length === 0 ? (
                <p css={tw`text-center text-neutral-400 mt-8`}>Harita bulunamadı.</p>
            ) : null}
        </ServerContentBlock>
    );
};
