import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const routes = [
  {
    path: "/",
    name: "Home",
    component: () => import("@/views/Home.vue"),
  },
  {
    path: "/yolo-ocean-camp",
    name: "YoloOceanCamp",
    component: () => import("@/views/YoloOceanCamp.vue"),
  },
  {
    path: "/about",
    name: "About",
    component: () => import("@/views/AboutView.vue"),
  },
  {
    path: "/rooms",
    name: "RoomList",
    component: () => import("@/views/rooms/RoomList.vue"),
  },
  {
    path: "/rooms/:id",
    name: "RoomDetail",
    component: () => import("@/views/rooms/RoomDetail.vue"),
  },
  {
    path: "/tours",
    name: "TourList",
    component: () => import("@/views/tours/TourList.vue"),
  },
  {
    path: "/tours/:id",
    name: "TourDetail",
    component: () => import("@/views/tours/TourDetail.vue"),
  },
  {
    path: "/car-rentals",
    name: "CarRentals",
    component: () => import("@/views/cars/CarRentalList.vue"),
  },
  {
    path: "/car-rentals/:id",
    name: "CarRentalDetail",
    component: () => import("@/views/cars/CarRentalDetail.vue"),
  },
  {
    path: "/blog",
    name: "BlogList",
    component: () => import("@/views/blog/BlogList.vue"),
  },
  {
    path: "/blog/:slug",
    name: "BlogPost",
    component: () => import("@/views/blog/BlogPost.vue"),
  },
  {
    path: "/restaurant",
    name: "Restaurant",
    component: () => import("@/views/Restaurant.vue"),
  },
  {
    path: "/login",
    name: "Login",
    component: () => import("@/views/auth/Login.vue"),
  },
  {
    path: "/register",
    name: "Register",
    component: () => import("@/views/auth/Register.vue"),
  },
  {
    path: "/profile",
    name: "Profile",
    component: () => import("@/views/user/Profile.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/booking-form",
    name: "BookingForm",
    component: () => import("@/views/BookingForm.vue"),
  },
  {
    path: "/booking-confirmation/:bookingNumber",
    name: "BookingConfirmation",
    component: () => import("@/views/BookingConfirmation.vue"),
  },
  {
    path: "/booking-lookup",
    name: "BookingLookup",
    component: () => import("@/views/BookingLookup.vue"),
  },
  {
    path: "/bookings",
    name: "Bookings",
    component: () => import("@/views/user/Bookings.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/payment/checkout",
    name: "PaymentCheckout",
    component: () => import("@/views/payment/Checkout.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/payment/qr",
    name: "QRPayment",
    component: () => import("@/views/payment/QRPayment.vue"),
  },
  {
    path: "/admin",
    component: () => import("@/views/admin/AdminLayout.vue"),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: "",
        name: "AdminDashboard",
        component: () => import("@/views/admin/Dashboard.vue"),
      },
      {
        path: "rooms",
        name: "AdminRooms",
        component: () => import("@/views/admin/Rooms.vue"),
      },
      {
        path: "tours",
        name: "AdminTours",
        component: () => import("@/views/admin/Tours.vue"),
      },
      {
        path: "car-rentals",
        name: "AdminCarRentals",
        component: () => import("@/views/admin/CarRentals.vue"),
      },
      {
        path: "bookings",
        name: "AdminBookings",
        component: () => import("@/views/admin/Bookings.vue"),
      },
      {
        path: "blog",
        name: "AdminBlog",
        component: () => import("@/views/admin/Blog.vue"),
      },
      {
        path: "guest-reviews",
        name: "AdminGuestReviews",
        component: () => import("@/views/admin/GuestReviews.vue"),
      },
      {
        path: "settings",
        name: "AdminSettings",
        component: () => import("@/views/admin/Settings.vue"),
      },
      {
        path: "menu-categories",
        name: "AdminMenuCategories",
        component: () => import("@/views/admin/MenuCategories.vue"),
      },
      {
        path: "menu-items",
        name: "AdminMenuItems",
        component: () => import("@/views/admin/MenuItems.vue"),
      },
      {
        path: "restaurant-settings",
        name: "AdminRestaurantSettings",
        component: () => import("@/views/admin/RestaurantSettings.vue"),
      },
      {
        path: "equipments",
        name: "AdminEquipments",
        component: () => import("@/views/admin/Equipments.vue"),
      },
      {
        path: "equipment-orders",
        name: "AdminEquipmentOrders",
        component: () => import("@/views/admin/EquipmentOrders.vue"),
      },
      {
        path: "nav-menus",
        name: "AdminNavMenus",
        component: () => import("@/views/admin/NavMenuManager.vue"),
      },
    ],
  },
  {
    path: "/payment/callback",
    name: "PaymentCallback",
    component: () => import("@/views/payment/Callback.vue"),
  },
  {
    path: "/payment/result",
    name: "PaymentResult",
    component: () => import("@/views/payment/Result.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  },
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next("/login");
  } else if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next("/");
  } else {
    next();
  }
});

export default router;
