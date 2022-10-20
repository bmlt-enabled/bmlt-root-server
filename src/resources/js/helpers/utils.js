import RootServerApi from "../RootServerApi";

export const loadAccessToken = () => {
    const token = localStorage.getItem("accessToken");
    if (token) {
        RootServerApi.accessToken = token;
    }
};
