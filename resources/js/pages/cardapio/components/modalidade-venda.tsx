import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { TipoVenda } from '@/lib/venda';

export function ModalidadeVenda({
    tipo = 'unidade',
    permiteExcesso = false,
    errors = {},
}: {
    tipo?: TipoVenda;
    permiteExcesso?: boolean;
    errors?: Record<string, string | undefined>;
}) {
    return (
        <div className="grid gap-4 sm:grid-cols-2">
            <div className="grid gap-2">
                <Label>Modalidade de venda</Label>
                <Select name="tipo_venda" defaultValue={tipo} required>
                    <SelectTrigger className="w-full">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="unidade">
                            Preço fixo por unidade
                        </SelectItem>
                        <SelectItem value="peso">
                            Por peso (preço por kg)
                        </SelectItem>
                        <SelectItem value="pessoa">
                            À vontade (por pessoa)
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError message={errors.tipo_venda} />
            </div>
            <div className="grid gap-2">
                <Label>Cobrança opcional de excesso de carne</Label>
                <Select
                    name="permite_excesso_carne"
                    defaultValue={permiteExcesso ? '1' : '0'}
                    required
                >
                    <SelectTrigger className="w-full">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="0">Desabilitada</SelectItem>
                        <SelectItem value="1">
                            Perguntar ao registrar pedido
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError message={errors.permite_excesso_carne} />
            </div>
        </div>
    );
}
