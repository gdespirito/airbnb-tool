export type Contact = {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    role: 'cleaning' | 'handyman' | 'other';
    notes: string | null;
    cleaning_tasks?: CleaningTask[];
    properties?: Property[];
    created_at: string;
    updated_at: string;
};

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
    cleaning_contact_id: number | null;
    cleaning_contact_name: string | null;
    cleaning_contact_phone: string | null;
    cleaning_contact?: Contact;
    metadata: Record<string, unknown> | null;
    upcoming_reservations_count?: number;
    reservations?: Reservation[];
    created_at: string;
    updated_at: string;
};

export type CleaningTask = {
    id: number;
    property_id: number;
    reservation_id: number | null;
    contact_id: number | null;
    status: 'pending' | 'notified' | 'in_progress' | 'completed' | 'verified';
    cleaning_type: 'checkout' | 'deep_clean' | 'touch_up';
    cleaning_fee: number | null;
    scheduled_date: string;
    assigned_to: string | null;
    assigned_phone: string | null;
    notes: string | null;
    property?: Property;
    reservation?: Reservation;
    contact?: Contact;
    created_at: string;
    updated_at: string;
};

export type ReservationNote = {
    id: number;
    reservation_id: number;
    content: string;
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
    reservation_notes?: ReservationNote[];
    created_at: string;
    updated_at: string;
};
