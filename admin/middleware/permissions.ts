export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore();
  const user = authStore.user;

  const requiredPermissions = to.meta.requiredPermissions as
    | string[]
    | undefined;

  if (!requiredPermissions || requiredPermissions.length === 0) {
    return;
  }

  if (!user || !user.role || !user.role.permissions) {
    return navigateTo("/");
  }

  const hasAllPermissions = requiredPermissions.every((requiredPermission) => {
    return user.role.permissions.some((permission) => {
      return permission.name === requiredPermission;
    });
  });

  if (!hasAllPermissions) {
    return navigateTo("/");
  }
});
