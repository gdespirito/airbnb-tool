export type Property = {
    id: number;
    name: string;
    slug: string;
    airbnb_url: string | null;
    airbnb_listing_id: string | null;
    ical_url: string | null;
    location: string;
    latitude: number | null;
    longitude: number | null;
    checkin_time: string;
    checkout_time: string;
    cleaning_contact_name: string | null;
    cleaning_contact_phone: string | null;
    metadata: Record<string, unknown> | null;
    upcoming_reservations_count?: number;
    reservations?: Reservation[];
    created_at: string;
    updated_at: string;
};

export type Reservation = {
    id: number;
    property_id: number;
    airbnb_reservation_id: string | null;
    guest_name: string;
    guest_phone: string | null;
    guest_email: string | null;
    number_of_guests: number;
    check_in: string;
    check_out: string;
    status: 'confirmed' | 'checked_in' | 'checked_out' | 'cancelled';
    notes: string | null;
    source: string;
    is_same_day_turnover?: boolean;
    property?: Property;
    created_at: string;
    updated_at: string;
};
