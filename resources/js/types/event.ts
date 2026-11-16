export type ContributionType = 'photo' | 'video' | 'voice' | 'wish';
export type GuestAlbumTab = 'album' | 'reels' | 'notes' | 'info';
export type GuestSection =
    | 'cover'
    | 'album'
    | 'reels'
    | 'catatan'
    | 'acara'
    | 'titip';

export type GuestEvent = {
    slug: string;
    category: 'wedding' | 'birthday' | 'engagement' | 'other';
    party_one_name: string;
    party_two_name: string | null;
    event_date: string | null;
    venue: string | null;
    notes?: string | null;
    cover_url: string | null;
    gallery_visibility: 'public' | 'private' | 'unlisted';
    theme: string;
    package_name: string | null;
    colors: { primary: string; accent: string; neutral: string };
    capabilities: {
        types: ContributionType[];
        photo_layouts: number[];
        max_duration_seconds: number;
        max_video_seconds: number;
        max_video_kb: number;
        max_photo_kb: number;
        max_voice_kb: number;
        max_voice_seconds: number;
        max_frames: number;
    };
};

export type GuestContribution = {
    id: string;
    type: ContributionType;
    layout: number | null;
    guest_name: string | null;
    visibility: 'public' | 'private';
    message: string | null;
    file_url: string | null;
    thumbnail_url: string | null;
    mime_type?: string | null;
    duration_seconds: number | null;
    created_at: string;
    width?: number | null;
    height?: number | null;
    dominant_color?: string | null;
    processing_status?: string;
    is_approved?: boolean;
};

export type ScrollMeta = {
    pageName: string;
    previousPage: string | null;
    nextPage: string | null;
    currentPage: string | null;
};

export type PaginatedContributions = {
    data: GuestContribution[];
    meta: ScrollMeta;
};

export type GuestExperienceProps = {
    navigation: {
        section: GuestSection;
        tab: GuestAlbumTab;
        view: 'feed' | 'grid';
        filter: 'all' | 'wish' | 'voice';
    };
    event: GuestEvent;
    contributions: PaginatedContributions | GuestContribution[];
    isOpen: boolean;
    canViewGallery: boolean;
    canViewPrivate: boolean;
    ownerToken?: string | null;
};
