<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import InputError from '@/components/InputError.vue';
import users from '@/routes/users';

const isOpen = ref(false);

const form = useForm({
  name: '',
  email: '',
});

const openDialog = () => {
  form.reset();
  form.clearErrors();
  isOpen.value = true;
};

const submit = () => {
  form.post(users.store().url, {
    onSuccess: () => {
      isOpen.value = false;
      form.reset();
    },
  });
};

defineExpose({ openDialog });
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:max-w-[425px] bg-zinc-950 border-zinc-800 text-zinc-100">
      <DialogHeader>
        <DialogTitle>Создание пользователя</DialogTitle>
      </DialogHeader>
      <form @submit.prevent="submit" class="space-y-4 py-4">
        <div class="space-y-2">
          <Label for="name">Имя</Label>
          <Input id="name" v-model="form.name" type="text" placeholder="Полное имя" />
          <InputError :message="form.errors.name" />
        </div>
        <div class="space-y-2">
          <Label for="email">Email</Label>
          <Input id="email" v-model="form.email" type="email" placeholder="example@mail.com" />
          <InputError :message="form.errors.email" />
        </div>

        <p class="text-xs text-zinc-500 mt-2">
          Пароль будет сгенерирован автоматически и показан в уведомлении после создания.
        </p>

        <DialogFooter class="pt-4 border-t border-zinc-800 gap-2 sm:gap-0">
          <Button type="button" variant="outline" @click="isOpen = false">Отмена</Button>
          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Создание...' : 'Создать' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
