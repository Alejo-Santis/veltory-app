import { toast } from 'svelte-sonner';

/**
 * Notificaciones de Veltory
 *
 * Uso:
 *   import { notify } from '../lib/toast';
 *
 *   notify.success('Producto guardado correctamente');
 *   notify.error('No se pudo eliminar el producto');
 *   notify.warning('Stock por debajo del mínimo');
 *   notify.info('Recuerda actualizar el stock');
 *   notify.loading('Guardando cambios...');   // retorna un id
 *   notify.dismiss(id);                       // cierra un toast por id
 *   notify.promise(promesa, { loading, success, error });
 */
export const notify = {
    success: (msg, opts) => toast.success(msg, opts),
    error:   (msg, opts) => toast.error(msg, opts),
    warning: (msg, opts) => toast.warning(msg, opts),
    info:    (msg, opts) => toast.info(msg, opts),
    loading: (msg, opts) => toast.loading(msg, opts),
    dismiss: (id)        => toast.dismiss(id),
    promise: (promise, messages, opts) => toast.promise(promise, messages, opts),
};
