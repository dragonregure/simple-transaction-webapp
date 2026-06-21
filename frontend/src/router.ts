import { createRouter, createWebHistory } from "vue-router";
import AppLayout from "@/components/layout/AppLayout.vue";
import TransactionsPage from "@/pages/TransactionsPage.vue";
import ReportsPage from "@/pages/ReportsPage.vue";
import ChartOfAccountsPage from "@/pages/ChartOfAccountsPage.vue";
import ChartOfAccountCategoriesPage from "@/pages/ChartOfAccountCategoriesPage.vue";

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: "/",
      component: AppLayout,
      children: [
        { path: "", redirect: "/transactions" },
        { path: "transactions", component: TransactionsPage },
        { path: "reports", component: ReportsPage },
        { path: "master/chart-of-accounts", component: ChartOfAccountsPage },
        { path: "master/chart-of-account-categories", component: ChartOfAccountCategoriesPage },
      ],
    },
    { path: "/:pathMatch(.*)*", redirect: "/transactions" },
  ],
});
