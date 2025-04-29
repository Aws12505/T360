<template>
    <div class="p-6">
        <h1 class="text-xl mb-4">Send SMS</h1>
        
        <form @submit.prevent="sendSms">
            <div class="mb-4">
                <label for="to" class="block mb-1">Phone Number</label>
                <input 
                    id="to" 
                    v-model="form.to" 
                    type="text" 
                    class="w-full p-2 border rounded"
                    placeholder="+1234567890"
                    required
                />
            </div>
            
            <div class="mb-4">
                <label for="message" class="block mb-1">Message</label>
                <textarea 
                    id="message" 
                    v-model="form.message" 
                    rows="4" 
                    class="w-full p-2 border rounded"
                    placeholder="Enter your message here"
                    required
                ></textarea>
            </div>
            
            <button 
                type="submit" 
                class="px-4 py-2 bg-blue-500 text-white rounded"
                :disabled="processing"
            >
                {{ processing ? 'Sending...' : 'Send SMS' }}
            </button>
        </form>
        
        <div v-if="successMessage" class="mt-4 p-4 bg-green-100 text-green-700 rounded">
            {{ successMessage }}
        </div>
        
        <div v-if="errorMessage" class="mt-4 p-4 bg-red-100 text-red-700 rounded">
            {{ errorMessage }}
        </div>
    </div>
</template>

<script>
import { useForm } from '@inertiajs/vue3';

export default {
    setup() {
        const form = useForm({
            to: '',
            message: ''
        });
        
        return {
            form,
            processing: false,
            successMessage: null,
            errorMessage: null,
            
            sendSms() {
                this.processing = true;
                this.successMessage = null;
                this.errorMessage = null;
                
                form.post(route('twilio.send-sms'), {
                    preserveScroll: true,
                    onSuccess: () => {
                        this.processing = false;
                        this.successMessage = 'SMS sent successfully!';
                        form.reset();
                    },
                    onError: (errors) => {
                        this.processing = false;
                        this.errorMessage = 'Failed to send SMS. Please try again.';
                    }
                });
            }
        };
    }
};
</script>