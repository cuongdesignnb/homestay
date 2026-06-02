import api from "./api";

export const guestBookingService = {
  // Create booking without authentication
  createBooking(bookingData) {
    return api.post("/bookings", bookingData);
  },

  // Lookup booking by booking number and email
  lookupBooking(bookingNumber, guestEmail) {
    return api.get(`/bookings/lookup/${bookingNumber}`, {
      params: { guest_email: guestEmail },
    });
  },

  // Cancel booking by booking number and email
  cancelBooking(bookingNumber, guestEmail) {
    return api.put(`/bookings/cancel/${bookingNumber}`, {
      guest_email: guestEmail,
    });
  },

  // Get payment form data for guest booking
  getPaymentForm(paymentId) {
    return api.get(`/payments/${paymentId}/form`);
  },
};

export default guestBookingService;
