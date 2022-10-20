import { Dashboard } from "../pages/Dashboard";
import { MeetingFormats } from "../pages/MeetingFormats";
import { Meetings } from "../pages/Meetings";
import { MyAccount } from "../pages/MyAccount";
import { ServiceBodies } from "../pages/ServiceBodies";
import { Users } from "../pages/Users";

export const routes = [
    {
        path: "/",
        component: Dashboard,
    },
    {
        path: "/meetings",
        component: Meetings,
    },
    {
        path: "/service-bodies",
        component: ServiceBodies,
    },
    {
        path: "/users",
        component: Users,
    },
    {
        path: "/meeting-formats",
        component: MeetingFormats,
    },
    {
        path: "/my-account",
        component: MyAccount,
    },
];
