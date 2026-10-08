import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { DadosVenda, TipoVenda } from '@/lib/venda';

type Props = {
    id: string;
    tipo: TipoVenda;
    permiteExcesso: boolean;
    dados: DadosVenda;
    onChange: (dados: Partial<DadosVenda>) => void;
    errors?: Record<string, string | undefined>;
};

export function CamposVenda({
    id,
    tipo,
    permiteExcesso,
    dados,
    onChange,
    errors = {},
}: Props) {
    return (
        <div className="grid gap-3">
            {tipo === 'peso' && (
                <div className="grid gap-1.5">
                    <Label htmlFor={`peso-${id}`}>
                        Peso líquido por porção (g)
                    </Label>
                    <Input
                        id={`peso-${id}`}
                        type="number"
                        min={1}
                        max={10000}
                        step={1}
                        inputMode="numeric"
                        value={dados.peso_gramas}
                        onChange={(event) =>
                            onChange({ peso_gramas: event.target.value })
                        }
                        placeholder="Ex.: 450"
                        required
                    />
                    <p className="text-xs text-muted-foreground">
                        Informe somente o alimento, descontando o prato ou a
                        embalagem. Para pesos diferentes, registre pedidos
                        separados.
                    </p>
                    <InputError message={errors.peso_gramas} />
                </div>
            )}
            {permiteExcesso && (
                <div
                    role="alert"
                    className="grid gap-2 rounded-md border border-amber-500/40 bg-amber-500/10 p-3"
                >
                    <Label htmlFor={`carne-${id}`}>
                        Cobrar excesso de carne?
                    </Label>
                    <Select
                        value={
                            dados.cobrar_excesso_carne === null
                                ? 'pendente'
                                : dados.cobrar_excesso_carne
                                  ? 'sim'
                                  : 'nao'
                        }
                        onValueChange={(value) =>
                            onChange({
                                cobrar_excesso_carne: value === 'sim',
                                adicional_carne:
                                    value === 'sim'
                                        ? dados.adicional_carne
                                        : '',
                            })
                        }
                    >
                        <SelectTrigger id={`carne-${id}`} className="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="pendente" disabled>
                                Escolha uma opção
                            </SelectItem>
                            <SelectItem value="nao">Não cobrar</SelectItem>
                            <SelectItem value="sim">
                                Sim, informar valor
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError message={errors.cobrar_excesso_carne} />
                    {dados.cobrar_excesso_carne && (
                        <div className="grid gap-1.5">
                            <Label htmlFor={`adicional-${id}`}>
                                Adicional de carne por porção/pessoa (R$)
                            </Label>
                            <Input
                                id={`adicional-${id}`}
                                type="number"
                                min="0.01"
                                max="99999.99"
                                step="0.01"
                                inputMode="decimal"
                                value={dados.adicional_carne}
                                onChange={(event) =>
                                    onChange({
                                        adicional_carne: event.target.value,
                                    })
                                }
                                required
                            />
                            <InputError message={errors.adicional_carne} />
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
